<?php declare(strict_types=1);

namespace TinyFramework\Queue;

use Redis;
use Predis\Client as Predis;

class RedisQueue implements QueueInterface
{

    /** @var Predis|Redis */
    private $redis;

    private array $config = [];

    public function __construct(array $config = [])
    {
        $this->config['scheme'] = $config['scheme'] ?? 'tcp';
        $this->config['host'] = $config['host'] ?? '127.0.0.1';
        $this->config['port'] = (int)($config['port'] ?? 6379);
        $this->config['password'] = $config['password'] ?? null;
        $this->config['database'] = (int)($config['database'] ?? 0);
        $this->config['timeout'] = max(1, (int)($config['timeout'] ?? 1));
        $this->config['read_write_timeout'] = (int)($config['read_write_timeout'] ?? -1);
        $this->config['profile'] = $config['profile'] ?? '2.6';
        $this->config['prefix'] = $config['prefix'] ?? 'queue:';
        $this->config['name'] = $config['name'] ?? 'default';

        if (class_exists(Redis::class)) {
            $this->redis = new Redis();
            if (!$this->redis->pconnect($this->config['host'], $this->config['port'])) {
                throw new \RuntimeException('Could not connect to redis');
            }
            $this->redis->select($this->config['database']);
            $this->redis->setOption(Redis::OPT_PREFIX, $this->config['prefix']);
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE);
            $this->redis->setOption(Redis::OPT_READ_TIMEOUT, $this->config['read_write_timeout']);
        } else {
            $this->redis = new Predis(
                [
                    'scheme' => $this->config['scheme'],
                    'host' => $this->config['host'],
                    'port' => $this->config['port'],
                    'password' => $this->config['password'],
                    'database' => $this->config['database'],
                    'timeout' => $this->config['timeout'],
                    'read_write_timeout' => $this->config['read_write_timeout']
                ],
                [
                    'profile' => $this->config['profile'],
                    'prefix' => $this->config['prefix'],
                ]
            );
        }
    }

    public function name(string $name = null)
    {
        if (is_null($name)) {
            return $this->config['name'];
        }
        $config = $this->config;
        $config['name'] = $name;
        return new self($config);
    }

    /**
     * @see https://redis.io/commands/rpush
     * @param JobInterface $job
     * @return QueueInterface
     */
    public function push(JobInterface $job): QueueInterface
    {
        if (!$job->delay()) {
            return $this->repush($job);
        }
        $queue = method_exists($job, 'queue') ? $job->queue() : $this->config['name'];
        $data = serialize($job);
        $queue = $queue . ':delayed';
        $ttl = time() + $job->delay();
        if ($this->redis instanceof Redis) {
            $this->redis->zAdd($queue, $ttl, $data);
        } else {
            $this->redis->zadd($queue, [$data => $ttl]);
        }
        return $this;
    }

    private function repush(JobInterface $job): QueueInterface
    {
        $queue = method_exists($job, 'queue') ? $job->queue() : $this->config['name'];
        $data = serialize($job);
        if ($this->redis instanceof Redis) {
            $this->redis->rPush($queue, $data);
        } else {
            $this->redis->rpush($queue, [$data]);
        }
        return $this;
    }

    /**
     * @see https://redis.io/commands/blpop
     * @param int $timeout
     * @return JobInterface|null
     */
    public function pop(int $timeout = 1): ?JobInterface
    {
        $this->fetchDelayed();
        if ($this->redis instanceof Redis) {
            $result = $this->redis->blPop([$this->config['name']], $timeout);
        } else {
            $result = $this->redis->blpop([$this->config['name']], $timeout);
        }
        if (!is_array($result)) {
            return null;
        }
        if (count($result) < 2) {
            return null;
        }
        if ($result[1]) {
            return unserialize($result[1]);
        }
        return null;
    }

    private function fetchDelayed(): void
    {
        // @TODO optimize to Redis LUA script
        $queue = $this->config['name'] . ':delayed';
        if ($this->redis instanceof Redis) {
            $jobs = $this->redis->zRangeByScore($queue, '0', (string)time());
        } else {
            $jobs = $this->redis->zrangebyscore($queue, 0, time());
        }
        if (is_array($jobs)) {
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
        return $this->redis->llen($this->config['name']);
    }

}
