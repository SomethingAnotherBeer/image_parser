<?php

namespace App\Controller;

use App\Exception\RequiredParamMissedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\ImageService;
use Symfony\Component\HttpFoundation\Request;
use App\Helper\ValidatorHelper;


class ImageController extends AbstractController
{
    use ValidatorHelper;


    private ImageService $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    #[Route('/images/parse', methods: ['POST'])]
    public function parseImagesFromUrl(Request $request): JsonResponse
    {   

        $request_body = $request->getPayload()->all();

        if (!array_key_exists('page_url', $request_body)) {
            throw new RequiredParamMissedException("Требуемый параметр 'page_url' не найден в теле запроса");
        }

        $page_url = $request_body['page_url'];
        $this->validateParamByRules($page_url, ['nonempty', 'url']);

        $page_url = urldecode($page_url);

        $response = $this->imageService->parseImagesFromUrl($page_url);
    
        return $this->json($response, 201)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }


    #[Route('/images/task/{task_key}/status', methods: ['GET'])]
    public function getImageTaskStatus(string $task_key): JsonResponse
    {
        $response = $this->imageService->getImageTaskStatus($task_key);

        return $this->json($response, 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }

    #[Route('images/getparsed', methods: ['GET'])]
    public function getParseImagesForUrl(Request $request): JsonResponse
    {   
        $request_params = $request->query->all();

        if (!array_key_exists('page_url', $request_params)) {
            throw new RequiredParamMissedException("Требуемый параметр 'page_url' не найден в параметрах запроса");
        }
        
        $parsed_page = $request_params['page_url'];
        $parsed_page = urldecode($parsed_page);

        $this->validateParamByRules($parsed_page, ['nonempty', 'url']);

        $response = $this->imageService->getParsedImages($parsed_page);

        return $this->json($response, 200)->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }




}
