<?php
namespace App\Service;

use App\Exception\ImagesNotParsedException;
use App\Exception\ResourceNotFoundException;
use App\Exception\TaskNotFoundException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Factory\SystemFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ImageParserNotification;
use App\Helper\TaskHelper;

use Redis;

class ImageService
{
    use TaskHelper;

    private HttpClientInterface $httpClient;
    private MessageBusInterface $bus;
    private Redis $redis;


    public function __construct(HttpClientInterface $httpClient, MessageBusInterface $bus)
    {
        $this->httpClient = $httpClient;
        $this->bus = $bus;
        $this->redis = SystemFactory::makeRedis();
    }


    public function getImageTaskStatus(string $task_key): array
    {
        $requested_task = $this->redis->hGetAll($task_key);

        if (!$requested_task) {
            throw new TaskNotFoundException("Запрашиваемая задача не найдена");

        }

        return ['current_status' => $requested_task['status']];
    }


    public function getParsedImages(string $url): array
    {
        $prepared_images = $this->redis->hGetAll($url);

        if (!$prepared_images) {
            throw new ImagesNotParsedException("Результат парсинга изображений по запрашиваемому ресурсу не найден");
        }


        return ['prepared_images' => $prepared_images['entries']];
    }



    public function parseImagesFromUrl(string $url): array
    {
        $response = $this->httpClient->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new ResourceNotFoundException("Запрашиваемый ресурс не найден");
        }


        $crawler = new Crawler($response->getContent());

        $images = $crawler->filter('img');
        $prepared_srcs = [];

        foreach ($images as $image) {
            if ($image instanceof \DOMElement) {

                $src = $image->getAttribute('src');  

                if ($src) {

                    if (preg_match("/^\/[A-Za-z0-9_-]+/", $src)) {
                        $host_name = parse_url($url)['host'];
                        $src = 'https:' . $host_name . $src;
                    }

                    if (!preg_match("/^https:/", $src)) {
                        $src = "https:" . $src;
                    }

                    if (preg_match("/^https:[\/]{1,2}/", $src)) {
                        $prepared_srcs[] = $src;
                    }

                }
            }
        }


        $task_key = $this->generateTaskKeyWithRetry($this->redis);

        $image_parser_params = [
            'task_key' => $task_key,
            'remote_url' => $url,
            'images_srcs' => $prepared_srcs,

        ];

        $this->redis->HSET($task_key, 'status', false);

        $this->bus->dispatch(new ImageParserNotification($image_parser_params));


        return ['task_key' => $task_key, 'remote_url' => $url];

    }


}