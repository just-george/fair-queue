<?php

declare(strict_types=1);

namespace Fair\Queue\Namer;

use Fair\Queue\Broker\Broker;
use Fair\Queue\Message\MessageInterface;

/**
 * Gives per fair-tag unique names.
 *
 * @see MessageInterface::getFairTag()
 * @see Broker::push()
 */
interface QueueNamerInterface
{
    /**
     * @return string must be non-empty unique per fair-tag string
     */
    public function getQueueName(string $fairTag): string;
}
