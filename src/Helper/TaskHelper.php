<?php
namespace App\Helper;
use Redis;


trait TaskHelper
{
    protected function generateTaskKeyWithRetry(Redis $redis): string
    {
        $task_key = $this->generateTaskKey();

        while ($redis->get($task_key)) {
            $task_key = $this->generateTaskKey();
        }
        
        return $task_key;
    }


    protected function generateTaskKey(): string
    {
        $key_args = [];
        $key_len = 10;
        $current_char_group = 0;

        for ($i = 0; $i < $key_len; $i++) {
            $current_char_group = rand(1, 3);
            
            switch($current_char_group) {
                case 1:
                    $key_args[] = chr(rand(65, 90));
                    break;
                case 2: 
                    $key_args[] = chr(rand(97, 122));
                    break;

                case 3:
                    $key_args[] = rand(0, 9);
                    break;
            }

        }

        return implode('', $key_args);

    }


}