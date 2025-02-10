<?php

declare(strict_types=1);

namespace Fair\Queue\Test\Integration;

use Codeception\Attribute\Depends;
use Fair\Queue\Broker\Broker;
use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Namer\PrefixQueueNamer;
use Fair\Queue\Serializer\PhpMessageSerializer;
use Fair\Queue\Storage\RedisStorage;
use Fair\Queue\Test\Support\IntegrationTester;
use Fair\Queue\Test\Support\Message;

class RedisStorageBrokerTestCest
{
    protected Broker $broker;
    protected PrefixQueueNamer $namer;
    protected PhpMessageSerializer $serializer;
    protected string $queuesListName;
    protected string $queuesSetName;

    /**
     * @SuppressWarnings("PHPMD.CamelCaseParameterName")
     * @SuppressWarnings("PHPMD.CamelCaseMethodName")
     * @SuppressWarnings("PHPMD.CamelCaseVariableName")
     */
    public function _before(IntegrationTester $I): void
    {
        $redis = $I->getRedis();
        $faker = $I->getFaker();
        $queuesListName = $faker->word();
        $queuesSetName = $faker->word();
        $serializer = new PhpMessageSerializer();
        $storage = new RedisStorage(
            $redis,
            $serializer,
            $queuesListName,
            $queuesSetName,
        );
        $namer = new PrefixQueueNamer();
        $broker = new Broker(
            $storage,
            $namer,
        );
        $this->broker = $broker;
        $this->namer = $namer;
        $this->serializer = $serializer;
        $this->queuesListName = $queuesListName;
        $this->queuesSetName = $queuesSetName;
    }

    /**
     * @SuppressWarnings("PHPMD.CamelCaseParameterName")
     * @SuppressWarnings("PHPMD.CamelCaseVariableName")
     */
    public function testPush(IntegrationTester $I): void
    {
        $faker = $I->getFaker();
        $queuesCount = $faker->numberBetween(10, 20);
        $queues = [];
        $serializedMessages = [];
        for ($queueIndex = 0; $queueIndex < $queuesCount; ++$queueIndex) {
            $fairTag = sprintf('test-fair-tag-%d', $queueIndex);
            $queue = $this->namer->getQueueName($fairTag);
            $queues[] = $queue;
            $serializedMessages[$queue] = [];
            $messagesCount = $faker->numberBetween(1, 20);
            for ($messageIndex = 0; $messageIndex < $messagesCount; ++$messageIndex) {
                $message = new Message($fairTag);
                $serializedMessage = $this->serializer->serialize($message);
                $serializedMessages[$queue][] = $serializedMessage;
                $this->broker->push($message);
            }
        }
        $I->seeInRedis($this->queuesListName, $queues);
        $I->seeInRedis($this->queuesSetName, $queues);
        foreach ($queues as $queue) {
            $I->seeInRedis($queue, $serializedMessages[$queue]);
        }
    }

    /**
     * @SuppressWarnings("PHPMD.CamelCaseParameterName")
     * @SuppressWarnings("PHPMD.CamelCaseVariableName")
     */
    #[Depends('testPush')]
    public function testPop(IntegrationTester $I): void
    {
        $tag1 = 't1';
        $tag2 = 't2';
        $tag3 = 't3';
        $queue1 = $this->namer->getQueueName($tag1);
        $queue2 = $this->namer->getQueueName($tag2);
        $queue3 = $this->namer->getQueueName($tag3);
        $this->broker->push(new Message($tag1));
        $this->broker->push(new Message($tag1));
        $this->broker->push(new Message($tag1));
        $this->broker->push(new Message($tag1));
        $this->broker->push(new Message($tag2));
        $this->broker->push(new Message($tag2));
        $this->broker->push(new Message($tag3));

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag1, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue2, $queue3, $queue1]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2, $queue3]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag2, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue3, $queue1, $queue2]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2, $queue3]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag3, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue1, $queue2, $queue3]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2, $queue3]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag1, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue2, $queue3, $queue1]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2, $queue3]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag2, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue3, $queue1, $queue2]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2, $queue3]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag1, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue2, $queue1]);
        $I->seeInRedis($this->queuesSetName, [$queue1, $queue2]);

        $message = $this->broker->pop();
        $I->assertInstanceOf(MessageInterface::class, $message);
        $I->assertSame($tag1, $message->getFairTag());
        $I->seeInRedis($this->queuesListName, [$queue1]);
        $I->seeInRedis($this->queuesSetName, [$queue1]);

        $message = $this->broker->pop();
        $I->assertNull($message);
        $I->dontSeeInRedis($this->queuesListName);
        $I->dontSeeInRedis($this->queuesSetName);
    }
}
