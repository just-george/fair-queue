<?php

declare(strict_types=1);

namespace Fair\Queue\Namer;

use InvalidArgumentException;

class PrefixQueueNamer implements QueueNamerInterface
{
    public const DEFAULT_PREFIX = 'fair/queue';

    private string $prefix;

    public function __construct(
        string $prefix = self::DEFAULT_PREFIX,
    ) {
        $prefix = trim($prefix);
        if ($prefix === '') {
            $error = 'PrefixQueueNamer: prefix cannot be empty';
            throw new InvalidArgumentException($error);
        }
        $this->prefix = $prefix;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getQueueName(string $fairTag): string
    {
        $name = sprintf('%s.%s', $this->prefix, $fairTag);

        return $name;
    }
}
