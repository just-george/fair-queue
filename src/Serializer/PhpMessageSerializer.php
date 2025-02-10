<?php

declare(strict_types=1);

namespace Fair\Queue\Serializer;

use Fair\Queue\Message\MessageInterface;
use InvalidArgumentException;

/**
 * Simple message serializer utilizing PHP's serialize() and unserialize() functions.
 *
 * Using it on production is quite discouraging
 */
class PhpMessageSerializer implements MessageSerializerInterface
{
    public function serialize(MessageInterface $message): string
    {
        return serialize($message);
    }

    /**
     * @SuppressWarnings("PHPMD.ErrorControlOperator")
     */
    public function deserialize(string $serializedMessage): MessageInterface
    {
        $message = @unserialize($serializedMessage);
        if (!$message instanceof MessageInterface) {
            $error = sprintf(
                'cannot deserialize into MessageInterface: %s',
                $serializedMessage,
            );
            throw new InvalidArgumentException($error);
        }

        return $message;
    }
}
