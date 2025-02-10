<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Unit\Serializer;

use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Serializer\PhpMessageSerializer;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpMessageSerializer::class)]
class PhpMessageSerializerTest extends TestCase
{
    protected FakerGenerator $faker;
    protected PhpMessageSerializer $serializer;

    protected function setUp(): void
    {
        $this->faker = FakerFactory::create();
        $this->serializer = new PhpMessageSerializer();
    }

    /**
     * @SuppressWarnings("PHPMD.LongVariable")
     */
    public function testSerializeDeserialize(): void
    {
        $message = self::createStub(MessageInterface::class);

        $expectedSerializedMessage = serialize($message);
        $actualSerializedMessage = $this->serializer->serialize($message);
        self::assertSame($expectedSerializedMessage, $actualSerializedMessage);
        $actualDeserializedMessage = $this->serializer->deserialize(
            $actualSerializedMessage,
        );
        self::assertInstanceOf(
            MessageInterface::class,
            $actualDeserializedMessage,
        );
    }

    public function testDeserializeInvalidMessageThrowsException(): void
    {
        $invalidData = $this->faker->sentence();

        $this->expectException(InvalidArgumentException::class);
        $this->serializer->deserialize($invalidData);
    }
}
