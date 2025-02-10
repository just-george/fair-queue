<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Unit\Storage;

use Fair\Queue\Serializer\MessageSerializerInterface;
use Fair\Queue\Storage\RedisStorage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redis;

#[CoversClass(RedisStorage::class)]
class RedisStorageTest extends TestCase
{
    #[DataProvider('invalidConstructorArgumentsProvider')]
    public function testExceptionOnInvalidConstructorArgs(
        string $queuesListName,
        string $queuesSetName,
    ): void {
        $redis = self::createStub(Redis::class);
        $serializer = self::createStub(MessageSerializerInterface::class);

        $this->expectException(InvalidArgumentException::class);
        new RedisStorage(
            $redis,
            $serializer,
            $queuesListName,
            $queuesSetName,
        );
    }

    /**
     * @return array<array<string>>
     */
    public static function invalidConstructorArgumentsProvider(): iterable
    {
        yield ['', 'validSet'];
        yield [" \t\n", 'validSet'];
        yield ['validList', ''];
        yield ['validList', " \t\n"];
        yield ['', ''];
        yield [" \t\n", " \t\n"];
    }
}
