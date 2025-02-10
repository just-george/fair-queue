<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Support\Helper;

use Codeception\Module;
use Redis;

class RedisProvider extends Module
{
    /**
     * @var array<string,mixed>
     */
    protected array $config = [
        'host' => 'localhost',
        'port' => 6379,
    ];

    public function getRedis(): Redis
    {
        $host = $this->config['host'];
        $port = (int) $this->config['port'];
        $redis = new Redis([
            'host' => $host,
            'port' => $port,
        ]);

        return $redis;
    }
}
