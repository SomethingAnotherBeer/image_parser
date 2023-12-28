<?php
namespace App\Factory;
use \Redis AS Redis;
use Symfony\Component\Cache\Traits\RedisProxy;

class SystemFactory
{

    public static function makeRedis(): Redis
    {
        $redis_params = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'connectTimeout' => 2.5,
        ];


        return new Redis($redis_params);

    }

}