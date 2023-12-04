<?php

declare(strict_types=1);

namespace TinyFramework\Queue;

use Redis;
use RuntimeException;

class RedisQueue implements QueueInterface
{
    private Redis $redis;

    private array $config = [];

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        if (!\extension_loaded('redis')) {
            throw new \RuntimeException(sprintf(
                'You cannot use the "%s" as the "redis" extension is not installed.',
                __CLASS__
            ));
        }

        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['prefix'] = $config['prefix'] ?? 'queue:';
        $this->config['name'] = $config['name'] ?? 'default';

        $this->redis = new Redis();
        if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
            throw new RuntimeException('Could not connect to redis');
        }
        $this->redis->auth($this->config['password']);
        $this->redis->select($this->config['database']);
        $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
        $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
    }

    public function name(string $name = null): RedisQueue|string
    {
        if ($name === null) {
            return $this->config['name'];
        }
        $config = $this->config;
        $config['name'] = $name;
        return new self($config);
    }

    /**
     * @see https://redis.io/commands/rpush
     */
    public function push(JobInterface $job): static
    {
        if (!$job->delay()) {
            return $this->repush($job);
        }
        $data = serialize($job);
        $ttl = microtime(true) + $job->delay();
        $this->redis->zAdd($job->queue() . ':delayed', $ttl, $data);
        return $this;
    }

    private function repush(JobInterface $job): static
    {
        $data = serialize($job);
        $this->redis->rPush($job->queue(), $data);
        return $this;
    }

    public function pop(): JobInterface|null
    {
        $this->fetchDelayed();
        $result = $this->redis->lPop($this->config['name']);
        if (!is_string($result) || empty($result)) {
            return null;
        }
        return unserialize($result);
    }

    private function fetchDelayed(): void
    {
        // @TODO optimize to Redis LUA script
        $queue = $this->config['name'] . ':delayed';
        $jobs = $this->redis->zRangeByScore($queue, '0', (string)microtime(true));
        if (\is_array($jobs)) {
            foreach ($jobs as $job) {
                if ($this->redis->zrem($queue, $job)) {
                    $this->repush(unserialize($job));
                }
            }
        }
    }

    /**
     * @see https://redis.io/commands/llen
     * @return int
     */
    public function count(): int
    {
        $this->fetchDelayed();
        return (int)$this->redis->llen($this->config['name']);
    }

    public function ack(JobInterface $job): QueueInterface
    {
        return $this;
    }

    public function nack(JobInterface $job): QueueInterface
    {
        return $this;
    }
}
