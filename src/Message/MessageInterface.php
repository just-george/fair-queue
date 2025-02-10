<?php

declare(strict_types=1);

namespace Fair\Queue\Message;

use Fair\Queue\Broker\BrokerInterface;

/**
 * A data piece storing in a queue.
 */
interface MessageInterface
{
    /**
     * Arbitrary string identifier for fair messages for fair distribuion by
     * BrokerInterface.
     *
     * @see BrokerInterface
     */
    public function getFairTag(): string|null;
}
