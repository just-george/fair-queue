<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Unit\Broker;

use Fair\Queue\Broker\Broker;
use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Namer\QueueNamerInterface;
use Fair\Queue\Storage\StorageInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Broker::class)]
class BrokerTest extends TestCase
{
    protected StorageInterface&MockObject $storage;
    protected QueueNamerInterface&MockObject $queueNamer;
    protected Broker $broker;
    protected FakerGenerator $faker;

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->queueNamer = $this->createMock(QueueNamerInterface::class);
        $this->broker = new Broker($this->storage, $this->queueNamer);
        $this->faker = FakerFactory::create();
    }

    public function testPush(): void
    {
        $fairTag = $this->faker->word();
        $queueName = $this->faker->word();

        $message = self::createStub(MessageInterface::class);
        $message->method('getFairTag')->willReturn($fairTag);
        $this->queueNamer
            ->method('getQueueName')
            ->with($fairTag)
            ->willReturn($queueName)
        ;
        $this->storage
            ->expects(self::once())
            ->method('pushMessage')
            ->with($queueName, $message)
        ;
        $this->storage
            ->expects(self::once())
            ->method('pushQueue')
            ->with($queueName)
        ;

        $this->broker->push($message);
    }

    public function testPushWithNoFairTag(): void
    {
        $fairTag = null;
        $queueName = $this->faker->word();
        $message = self::createStub(MessageInterface::class);
        $message->method('getFairTag')->willReturn($fairTag);
        $this->queueNamer
            ->expects(self::once())
            ->method('getQueueName')
            ->with(Broker::DEFAULT_FAIR_TAG)
            ->willReturn($queueName)
        ;
        $this->storage
            ->expects(self::once())
            ->method('pushMessage')
            ->with($queueName, $message)
        ;
        $this->storage
            ->expects(self::once())
            ->method('pushQueue')
            ->with($queueName)
        ;

        $this->broker->push($message);
    }

    public function testPop(): void
    {
        $queueName = $this->faker->word();
        $message = self::createStub(MessageInterface::class);
        $this->storage
            ->expects(self::once())
            ->method('popPushQueue')
            ->willReturn($queueName)
        ;
        $this->storage
            ->expects(self::once())
            ->method('popMessage')
            ->with($queueName)
            ->willReturn($message)
        ;

        $actualMessage = $this->broker->pop();
        self::assertSame($message, $actualMessage);
    }

    public function testPopWhenNoQueue(): void
    {
        $queueName = null;
        $this->storage
            ->expects(self::once())
            ->method('popPushQueue')
            ->willReturn($queueName)
        ;
        $this->storage
            ->expects(self::never())
            ->method('popMessage')
        ;

        $actualMessage = $this->broker->pop();
        self::assertNull($actualMessage);
    }

    public function testPopBlocking(): void
    {
        $queueName = $this->faker->word();
        $timeout = $this->faker->randomFloat();
        $message = self::createStub(MessageInterface::class);
        $this->storage
            ->expects(self::once())
            ->method('popPushQueueBlocking')
            ->with($timeout)
            ->willReturn($queueName)
        ;
        $this->storage
            ->expects(self::once())
            ->method('popMessage')
            ->with($queueName)
            ->willReturn($message)
        ;

        $actualMessage = $this->broker->popBlocking($timeout);
        self::assertSame($message, $actualMessage);
    }

    public function testPopBlockingWhenNoQueue(): void
    {
        $queueName = null;
        $timeout = $this->faker->randomFloat();
        $this->storage
            ->method('popPushQueueBlocking')
            ->with($timeout)
            ->willReturn($queueName)
        ;

        $actualMessage = $this->broker->popBlocking($timeout);
        self::assertNull($actualMessage);
    }
}
