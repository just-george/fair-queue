<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Unit\Namer;

use Fair\Queue\Namer\PrefixQueueNamer;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrefixQueueNamer::class)]
class PrefixQueueNamerTest extends TestCase
{
    protected FakerGenerator $faker;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
    }

    #[DataProvider('invalidPrefixProvider')]
    public function testInvalidPrefixThrowsException(string $invalidPrefix): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PrefixQueueNamer($invalidPrefix);
    }

    /**
     * @return array<array<string>>
     */
    public static function invalidPrefixProvider(): iterable
    {
        yield [''];
        yield [" \t\n"];
    }

    public function testGetQueueName(): void
    {
        $prefix = $this->faker->word;
        $fairTag = $this->faker->word;
        $expectedQueueName = sprintf('%s.%s', $prefix, $fairTag);

        $namer = new PrefixQueueNamer($prefix);
        $actualQueueName = $namer->getQueueName($fairTag);
        self::assertSame($expectedQueueName, $actualQueueName);
    }
}
