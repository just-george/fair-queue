<?php

declare(strict_types=1);

namespace Fair\Queue\Broker;

use Fair\Queue\Message\MessageInterface;

/**
 * Receieves and provides messages/job from a queue in fair order.
 */
interface BrokerInterface
{
    public function push(MessageInterface $message): void;

    /**
     * Return the next message/job from a queue.
     *
     * Order of the messages retrieved must be according to a fair distribuion
     * based of MessageInterface::getFairTag(), which may be different than an
     * order of self::push() calls
     *
     * @see MessageInterface::getFairTag()
     *
     * @return MessageInterface|null NULL if there is no messages/jobs
     */
    public function pop(): MessageInterface|null;

    /**
     * Same as pop() but wait for up to $timeout seconds if there is no
     * messages/job.
     *
     * If there is a new message appearing during waiting time then it must be
     * returned immediately
     *
     * @param float $timeout Seconds. 0.0 for endless waiting (highly discouraging)
     *
     * @return MessageInterface|null NULL if no new messages appeared during
     *                               $timeout seconds of waiting
     */
    public function popBlocking(float $timeout): MessageInterface|null;
}
