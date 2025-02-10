# Fast messages/jobs queue with fair distribution

Shipped with [PhpRedis](https://github.com/phpredis/phpredis) BrokerInterface implementation.

Every message/job pushed into a queue has the fair-tag used to handle the message processing fairly, like so:

```php
/**
 * @var BrokerInterface $broker
 */
$message = new SomeMessageOrJob(somePayload: 'user1-job1', fairTag: 'user1');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user1-job2', fairTag: 'user1');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user2-job1', fairTag: 'user2');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user2-job2', fairTag: 'user2');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user3-job1', fairTag: 'user3');
$broker->push($message);

while (($message = $broker->pop()) !== null) {
    echo $message->getSomePayload() . "\n";
}
//Output (not same order as in input):
//user1-job1
//user2-job1
//user3-job1
//user1-job2
//user2-job2
```
## Requirements
 * PHP: ^8.1
 * Redis: ^5
 * [PhpRedis](https://github.com/phpredis/phpredis)

## Basic features

 * Fair messages distribution based on arbitrary string fair-tag
 * Messages with same fair-tag are handled in order of pushing
 * Pushing complexity: O(1)
 * Popping complexity: O(1) if there are no **empty** queues; O(m) where m is a number of **empty** (not all) queues (they are getting removed during pop operation)
 * Zero latency blocking popping support (forget about `sleep(1);`)
 * Endless flexibility: add as many workers as many connections your Redis instance can handle

## Blocking VS Non-blocking pop
### Non-blocking pop
 * returns `NULL` immediately if a queue is empty.
```php
// NOT SAFE in case of an empty queue! It would cause 100% CPU load + constant Redis instance requests
while (true) {
    $message = $broker->pop();
    echo $message->getSomePayload() . "\n";
    //sleep(1); <= a classic solution to solve the 100% CPU problem
}
```

### Blocking pop
 * returns a message immediately, if any.
 * else: waits for a new message for up to `float` timeout
 * returns `NULL` if there are no new messages when time is up, or returns a new message **immediately** (without waiting for the timeout to time up) if a new message appears.
```php
// SAFE in case of an empty queue! Zero resources (both CPU + network traffic to Redis) usage during waiting
$timeout = 1.23; //seconds
while (true) {
    $message = $broker->popBlocking($timeout);
    echo $message->getSomePayload() . "\n";
}
```


## Full working example
```php
<?php

declare(strict_types=1);

require_once './vendor/autoload.php';

use Fair\Queue\Broker\Broker;
use Fair\Queue\Message\MessageInterface;
use Fair\Queue\Namer\PrefixQueueNamer;
use Fair\Queue\Serializer\PhpMessageSerializer;
use Fair\Queue\Storage\RedisStorage;
use Redis;

class SomeMessageOrJob implements MessageInterface
{
    private string $somePayload;
    private string|null $fairTag;

    public function __construct(
        string $somePayload,
        string|null $fairTag = null,
    ) {
        $this->fairTag = $fairTag;
        $this->somePayload = $somePayload;
    }

    public function getFairTag(): string|null
    {
        return $this->fairTag;
    }

    public function getSomePayload(): string
    {
        return $this->somePayload;
    }
}

$redis = new Redis([
    'host' => 'redis',
    'port' => 6379,
]);
$serilizer = new PhpMessageSerializer();
$namer = new PrefixQueueNamer(prefix: 'fair/queue');
$storage = new RedisStorage($redis, $serilizer);
$broker = new Broker($storage, $namer);
$message = new SomeMessageOrJob(somePayload: 'user1-job1', fairTag: 'user1');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user1-job2', fairTag: 'user1');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user2-job1', fairTag: 'user2');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user2-job2', fairTag: 'user2');
$broker->push($message);
$message = new SomeMessageOrJob(somePayload: 'user3-job1', fairTag: 'user3');
$broker->push($message);

while (($message = $broker->pop()) !== null) {
    echo $message->getSomePayload() . "\n";
}
// Output:
// user1-job1
// user2-job1
// user3-job1
// user1-job2
// user2-job2
```

## Testing

There is [docker-compose.yaml](test/docker/docker-compose.yaml) for testing/development/experiments.

```sh
export REDIS_HOST=redis
export REDIS_PORT=6379
make test
```
