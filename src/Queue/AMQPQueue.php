<?php declare(strict_types=1);

namespace TinyFramework\Queue;

use AMQPChannel;
use AMQPConnection;
use AMQPEnvelope;
use AMQPExchange;

class AMQPQueue implements QueueInterface
{

    static private ?AMQPConnection $connection = null;

    private ?AMQPChannel $channel = null;

    private ?AMQPExchange $exchange = null;

    private ?AMQPExchange $exchangeDelayed = null;

    private array $queues = [];

    private array $config = [
        'host' => '127.0.0.1', // max. 1024 chars
        'port' => 5672,
        'vhost' => null, // max. 128 chars
        'login' => null, // max. 128 chars
        'password' => null, // max. 128 chars
        'prefix' => '',
        'name' => 'default',
    ];

    public function __construct(array $config = [])
    {
        if (!\extension_loaded('amqp')) {
            throw new \RuntimeException(sprintf(
                'You cannot use the "%s" as the "amqp" extension is not installed.',
                __CLASS__
            ));
        }
        $this->config = array_merge($this->config, $config);
    }

    public function name(string $name = null): QueueInterface|string
    {
        if ($name === null) {
            return $this->config['name'];
        }
        return new self(array_merge($this->config, ['name' => $name]));
    }

    public function count(): int
    {
        return $this->getQueue($this->config['name'])->declareQueue();
    }

    public function push(JobInterface $job): QueueInterface
    {
        $exchange = $job->delay() ? $this->getExchangeDelyed() : $this->getExchange();
        $queue = $this->getQueue($job->queue(), $job->delay());
        $queue->declareQueue();
        $queue->bind($exchange->getName(), $queue->getName(), []);
        $exchange->publish(
            serialize($job),
            $queue->getName(),
            AMQP_NOPARAM,
            [
                'content_type' => 'application/php-serialized; charset=UTF-8',
                'content_encoding' => 'UTF-8',
                'delivery_mode' => 2, // DELIVERY_MODE_PERSISTENT
                'timestamp' => microtime(true),
            ]
        );
        return $this;
    }

    public function pop(): JobInterface|null
    {
        $queue = $this->connect()->getQueue($this->config['name']);
        $queue->declareQueue();
        $envelope = $queue->get(AMQP_NOPARAM);
        if ($envelope === false) {
            return null;
        }

        assert($envelope instanceof \AMQPEnvelope, 'Invalid AMQPEnvelope.');
        $job = unserialize($envelope->getBody());
        assert($job instanceof JobInterface, 'Invalid Job.');
        $job->metadata('envelope', $envelope);
        $job->metadata('queue', $queue);
        return $job;
    }

    public function ack(JobInterface $job): QueueInterface
    {
        $envelope = $job->metadata('envelope');
        assert($envelope instanceof AMQPEnvelope, 'Invalid AMQPEnvelope.');
        $queue = $job->metadata('queue');
        assert($queue instanceof \AMQPQueue, 'Invalid AMQPQueue.');
        $queue->ack($envelope->getDeliveryTag());
        return $this;
    }

    public function nack(JobInterface $job): QueueInterface
    {
        $envelope = $job->metadata('envelope');
        assert($envelope instanceof AMQPEnvelope, 'Invalid AMQPEnvelope.');
        $queue = $job->metadata('queue');
        assert($queue instanceof \AMQPQueue, 'Invalid AMQPQueue.');
        $queue->nack($envelope->getDeliveryTag());
        return $this;
    }

    private function connect(): static
    {
        if (!(self::$connection instanceof AMQPConnection)) {
            self::$connection = new AMQPConnection($this->config);
            self::$connection->setConnectionName(implode(' ', [
                'tinyframework/0.1', // @TODO implement a better version
                'php/' . phpversion(),
                'php-amqp/' . phpversion('amqp'),
                '(' . gethostname() . '; PID:' . getmypid() . ')',
            ]));
        }
        if (!self::$connection->isConnected()) {
            self::$connection->pconnect();
        }
        if (!($this->channel instanceof AMQPChannel)) {
            $this->channel = new AMQPChannel(self::$connection);
            // @link https://github.com/symfony/amqp-messenger/blob/0755d69e70be3f35f83b1ad496be4d3c6a87558c/Transport/Connection.php#L502
            // @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos.prefetch-count
            $this->channel->setPrefetchCount(1);
        }
        return $this;
    }

    private function getExchange(): AMQPExchange
    {
        if (!$this->exchange) {
            $this->exchange = new AMQPExchange($this->connect()->channel);
            $this->exchange->setName($this->config['prefix'] . 'exchange');
            $this->exchange->setType(\AMQP_EX_TYPE_DIRECT);
            $this->exchange->setFlags(\AMQP_DURABLE);
            $this->exchange->declareExchange();
        }
        return $this->exchange;
    }

    private function getExchangeDelyed(): AMQPExchange
    {
        if (!$this->exchangeDelayed) {
            $this->exchangeDelayed = new AMQPExchange($this->connect()->channel);
            $this->exchangeDelayed->setName($this->config['prefix'] . 'exchange:delayed');
            $this->exchangeDelayed->setType(\AMQP_EX_TYPE_DIRECT);
            $this->exchangeDelayed->setFlags(\AMQP_DURABLE);
            $this->exchangeDelayed->declareExchange();
        }
        return $this->exchangeDelayed;
    }

    private function getQueue(string $name, int $delay = 0): \AMQPQueue
    {
        if (!array_key_exists($name, $this->queues)) {
            $queue = new \AMQPQueue($this->connect()->channel);
            $queue->setName($this->config['prefix'] . $name);
            $queue->setFlags(\AMQP_DURABLE);
            if ($delay) {
                $queue->setArgument('x-message-ttl', $delay * 1000);
                $queue->setArgument('x-expires', ($delay + 10) * 1000);
                $queue->setArgument('x-dead-letter-exchange', $this->config['prefix'] . 'exchange');
                $queue->setArgument('x-dead-letter-routing-key', $queue->getName());
                $queue->setName($queue->getName() . ':delayed:' . $delay);
            }
            $this->queues[$name] = $queue;
        }
        return $this->queues[$name];
    }

}

// @link https://github.com/symfony/amqp-messenger/blob/5.4/Transport/Connection.php
