<?php
namespace App\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Message\ImageParserNotification;
use App\Factory\SystemFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler()]
class ImageParserNotificationHandler
{

    private HttpClientInterface $httpClient;

    const AVAILABLE_MIME = 'image';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }


    public function __invoke(ImageParserNotification $imageParserMessage)
    {

        $redis = SystemFactory::makeRedis();

        $task_key = $imageParserMessage->getTaskKey();
        $remote_url = $imageParserMessage->getRemoteUrl();
        
        $images_srcs = $imageParserMessage->getImagesSrcs();


        $images_info = [];

        foreach ($images_srcs as $image_src) {
            $image_info = [];

            $response = $this->httpClient->request('GET', $image_src);
            
            if (200 === $response->getStatusCode()) {
                $response_headers = $response->getHeaders();
                $response_mime = $response_headers['content-type'][0];

                $response_base_mime = explode('/', trim($response_mime))[0];

                if (self::AVAILABLE_MIME === $response_base_mime) {
                    if (array_key_exists('content-length', $response_headers)) {
                        $image_info['src'] = $image_src;
                        $image_info['size'] = $response_headers['content-length'][0] / 1000000;
                        $images_info[] = $image_info;
                    }

                }
                
            }   

        }

        $redis->HSET($task_key, 'status', true);
        $redis->HSET($remote_url, 'entries', json_encode($images_info, JSON_UNESCAPED_SLASHES));

        $redis->expire($task_key, 3600);
        $redis->expire($remote_url, 86400);


    }

}