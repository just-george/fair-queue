<?php

declare(strict_types=1);

namespace Fair\Queue\Storage;

use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Serializer\MessageSerializerInterface;
use InvalidArgumentException;
use Redis;

class RedisStorage implements StorageInterface
{
    public const DEFAULT_QUEUES_LIST_NAME = 'fair/queue.queues-list';
    public const DEFAULT_QUEUES_SET_NAME = 'fair/queue.queues-set';
    protected const PUSH_QUEUE_SCRIPT = <<<LUA
        local queues_set_name = KEYS[1]
        local queues_list_name = KEYS[2]
        local queue = ARGV[1]
        if redis.call("SISMEMBER", queues_set_name, queue) ~= 0 then
            return
        end
        redis.call("SADD", queues_set_name, queue)
        redis.call("RPUSH", queues_list_name, queue)
    LUA;
    protected const POP_PUSH_QUEUE_SCRIPT = <<<LUA
        local queues_set_name = KEYS[1]
        local queues_list_name = KEYS[2]
        local queue = redis.call("LPOP", queues_list_name)
        while queue do
            if redis.call("LLEN", queue) > 0 then
                redis.call("RPUSH", queues_list_name, queue)
                return queue
            end
            redis.call("SREM", queues_set_name, queue)
            queue = redis.call("LPOP", queues_list_name)
        end
    LUA;

    protected Redis $redis;
    protected MessageSerializerInterface $serializer;
    private string $queuesListName;
    private string $queuesSetName;

    public function __construct(
        Redis $redis,
        MessageSerializerInterface $serializer,
        string $queuesListName = self::DEFAULT_QUEUES_LIST_NAME,
        string $queuesSetName = self::DEFAULT_QUEUES_SET_NAME,
    ) {
        $this->redis = $redis;
        $this->serializer = $serializer;
        $queuesSetName = trim($queuesSetName);
        if ($queuesSetName === '') {
            $error = 'RedisStorage: queuesSetName cannot be empty';
            throw new InvalidArgumentException($error);
        }
        $this->queuesSetName = $queuesSetName;
        $queuesListName = trim($queuesListName);
        if ($queuesListName === '') {
            $error = 'RedisStorage: queuesListName cannot be empty';
            throw new InvalidArgumentException($error);
        }
        $this->queuesListName = $queuesListName;
    }

    public function pushQueue(string $queue): void
    {
        $this->redis->eval(
            self::PUSH_QUEUE_SCRIPT,
            args: [
                $this->queuesSetName,
                $this->queuesListName,
                $queue,
            ],
            num_keys: 2,
        );
    }

    public function popPushQueue(): string|null
    {
        $queue = $this->redis->eval(
            self::POP_PUSH_QUEUE_SCRIPT,
            args: [
                $this->queuesSetName,
                $this->queuesListName,
            ],
            num_keys: 2,
        );
        if ($queue === false) {
            return null;
        }

        return $queue;
    }

    public function popPushQueueBlocking(float $timeout): string|null
    {
        $queuesCount = $this->redis->lLen($this->queuesListName);
        if ($queuesCount === 0) {
            $this->redis->blmove(
                $this->queuesListName,
                $this->queuesListName,
                Redis::LEFT,
                Redis::LEFT,
                $timeout,
            );
        }

        return $this->popPushQueue();
    }

    public function popMessage(string $queue): MessageInterface|null
    {
        $serializedMessage = $this->redis->lPop($queue);
        if ($serializedMessage === false) {
            return null;
        }
        $message = $this->serializer->deserialize($serializedMessage);

        return $message;
    }

    public function pushMessage(string $queue, MessageInterface $message): void
    {
        $serializedMessage = $this->serializer->serialize($message);
        $this->redis->rPush($queue, $serializedMessage);
    }
}
