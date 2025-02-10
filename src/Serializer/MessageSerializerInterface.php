<?php

declare(strict_types=1);

namespace Fair\Queue\Serializer;

use Fair\Queue\Message\MessageInterface;
use InvalidArgumentException;

interface MessageSerializerInterface
{
    public function serialize(MessageInterface $message): string;

    /**
     * @throws InvalidArgumentException Cannot deserialize the data given into
     *                                  MessageInterface
     */
    public function deserialize(string $serializedMessage): MessageInterface;
}
