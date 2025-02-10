<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Support;

use Fair\Queue\Message\MessageInterface;

class Message implements MessageInterface
{
    private string $fairTag;

    public function __construct(string $fairTag)
    {
        $this->fairTag = $fairTag;
    }

    public function getFairTag(): string|null
    {
        return $this->fairTag;
    }
}
