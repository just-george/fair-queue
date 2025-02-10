<?php

declare(strict_types=1);

namespace Fair\Queue\Storage;

use Fair\Queue\Message\MessageInterface;

/**
 * Stores and retrieves queues and their messages.
 */
interface StorageInterface
{
    /**
     * Stores new queue in a storage.
     */
    public function pushQueue(string $queue): void;

    /**
     * Retrieves the next queue using fair distribution without removing it.
     *
     * Should not return empty queues
     *
     * @return string|null Queue name or NULL if there are not queues
     */
    public function popPushQueue(): string|null;

    /**
     * Same as popPushQueue but waits for up to $timeout seconds if there is no
     * queues.
     *
     * If there is a new non-empty queue appearing during waiting time then it
     * must be returned immediately
     *
     * @param float $timeout seconds
     *
     * @return string|null NULL if there is no non-empty queues after $timeout
     *                     seconds
     */
    public function popPushQueueBlocking(float $timeout): string|null;

    /**
     * Retrieve the oldest message from the given queue.
     *
     * The oldest message is one that was pushed first
     *
     * @return MessageInterface|null NULL if there is no messages in the given
     *                               queue
     */
    public function popMessage(string $queue): MessageInterface|null;

    public function pushMessage(string $queue, MessageInterface $message): void;
}
