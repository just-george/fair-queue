<?php

declare(strict_types=1);

namespace Fair\Queue\Broker;

use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Namer\QueueNamerInterface;
use Fair\Queue\Storage\StorageInterface;

class Broker implements BrokerInterface
{
    public const DEFAULT_FAIR_TAG = 'default';

    protected StorageInterface $storage;
    protected QueueNamerInterface $queueNamer;

    public function __construct(
        StorageInterface $storage,
        QueueNamerInterface $queueNamer,
    ) {
        $this->storage = $storage;
        $this->queueNamer = $queueNamer;
    }

    public function push(MessageInterface $message): void
    {
        $fairTag = $message->getFairTag() ?? self::DEFAULT_FAIR_TAG;
        $queue = $this->queueNamer->getQueueName($fairTag);
        $this->storage->pushMessage($queue, $message);
        $this->storage->pushQueue($queue);
    }

    public function pop(): MessageInterface|null
    {
        $queue = $this->storage->popPushQueue();
        if ($queue === null) {
            return null;
        }
        $message = $this->storage->popMessage($queue);

        return $message;
    }

    public function popBlocking(float $timeout): MessageInterface|null
    {
        $queue = $this->storage->popPushQueueBlocking($timeout);
        if ($queue === null) {
            return null;
        }
        $message = $this->storage->popMessage($queue);

        return $message;
    }
}
