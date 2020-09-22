<?php declare(strict_types=1);

namespace TinyFramework\Session;

use Redis;
use Predis\Client as Predis;

class RedisSession extends SessionAwesome implements SessionInterface
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
        $this->config['prefix'] = $config['prefix'] ?? 'session:';
        $this->config['ttl'] = (int)($this->config['ttl'] ?? 300);

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

    public function open(?string $id): SessionInterface
    {
        if ($id) {
            $this->id = $id;
        }
        if ($this->redis->exists($this->getId())) {
            if ($value = $this->redis->get($this->getId())) {
                $this->data = unserialize($value);
            }
        }
        return $this;
    }

    public function close(): SessionInterface
    {
        $this->redis->setex($this->getId(), $this->config['ttl'], serialize($this->data));
        return $this;
    }

    public function destroy(): SessionInterface
    {
        if ($this->redis->exists($this->getId())) {
            $this->redis->del($this->getId());
        }
        return $this;
    }

    public function clear(): SessionInterface
    {
        $deleteKeys = $this->redis->keys('*');
        if (count($deleteKeys)) {
            $this->redis->del($deleteKeys);
        }
        return $this;
    }

}
