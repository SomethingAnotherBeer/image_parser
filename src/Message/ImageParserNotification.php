<?php
namespace App\Message;

class ImageParserNotification
{
    private string $task_key;
    private string $remote_url;
    private array $images_srcs;


    public function __construct(array $params)
    {
        $this->task_key = $params['task_key'];
        $this->remote_url = $params['remote_url'];
        $this->images_srcs = $params['images_srcs'];
    }


    public function getTaskKey(): string
    {
        return $this->task_key;
    }

    public function getRemoteUrl(): string
    {
        return $this->remote_url;
    }


    public function getImagesSrcs(): array
    {
        return $this->images_srcs;
    }

}