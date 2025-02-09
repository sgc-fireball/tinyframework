<?php

namespace TinyFramework\Tests\Cache;

use PHPUnit\Framework\TestCase;
use Redis;
use TinyFramework\Cache\RedisCache;

class RedisCacheTest extends TestCase
{

    public function testCache(): void
    {
        $redis = new Redis();
        $redis->connect('redis');
        $redis->select(15);
        $redis->flushDB();
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $this->assertTrue(in_array($redis->exists('test'), [0, false], true));

        $cache = new RedisCache(['host' => 'redis', 'database' => 15, 'prefix' => '']);
        $test = sha1(microtime(true));
        $cache->set('test', $test);

        $this->assertTrue(in_array($redis->exists('test'), [1, true], true));
        $this->assertEquals($test, $redis->get('test'));
    }

    public function testTags(): void
    {
        $redis = new Redis();
        $redis->connect('redis');
        $redis->select(15);
        $redis->flushDB();
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $this->assertTrue(in_array($redis->exists('test'), [0, false], true));

        $cache = new RedisCache(['host' => 'redis', 'database' => 15, 'prefix' => '']);
        $cache = $cache->tag(['test1', 'test2']);

        $test = sha1(microtime(true));
        $cache->set('test', $test);

        $this->assertTrue(in_array($redis->exists('test'), [1, true], true));
        $this->assertEquals($test, $redis->get('test'));

        $this->assertTrue(in_array($redis->exists('tag:test1'), [1, true], true));
        $tags = $redis->lrange('tag:test1', 0, -1);
        $this->assertIsArray($tags);
        $this->assertTrue(in_array('test', $tags));

        $this->assertTrue(in_array($redis->exists('tag:test2'), [1, true], true));
        $tags = $redis->lrange('tag:test2', 0, -1);
        $this->assertIsArray($tags);
        $this->assertTrue(in_array('test', $tags));

        $this->assertTrue(in_array($redis->exists('tag:test3'), [0, false], true));
    }

    public function testPrefixWithTags(): void
    {
        $redis = new Redis();
        $redis->connect('redis');
        $redis->select(15);
        $redis->flushDB();
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $this->assertTrue(in_array($redis->exists('test'), [0, false], true));

        $cache = new RedisCache(['host' => 'redis', 'prefix' => 'test:cache', 'database' => 15]);
        $cache = $cache->tag(['test1', 'test2']);

        $test = sha1(microtime(true));
        $cache->set('test', $test);

        $this->assertTrue(in_array($redis->exists('test:cache:test'), [1, true], true));
        $this->assertEquals($test, $redis->get('test:cache:test'));

        $this->assertTrue(in_array($redis->exists('test:cache:tag:test1'), [1, true], true));
        $tags = $redis->lrange('test:cache:tag:test1', 0, -1);
        $this->assertIsArray($tags);
        $this->assertTrue(in_array('test:cache:test', $tags));

        $this->assertTrue(in_array($redis->exists('test:cache:tag:test2'), [1, true], true));
        $tags = $redis->lrange('test:cache:tag:test2', 0, -1);
        $this->assertIsArray($tags);
        $this->assertTrue(in_array('test:cache:test', $tags));

        $this->assertTrue(in_array($redis->exists('test:cache:tag:test3'), [0, false], true));
    }

    public function testClear(): void
    {
        $redis = new Redis();
        $redis->connect('redis');
        $redis->select(15);
        $redis->flushDB();
        $this->assertTrue(in_array($redis->exists('test'), [0, false], true));
        $redis->set('redis', 'redis');
        $this->assertTrue(in_array($redis->exists('redis'), [1, true], true));

        $cache = new RedisCache(['host' => 'redis', 'prefix' => 'testClear', 'database' => 15]);
        $cache->set('test', '1234');
        $this->assertTrue(in_array($redis->exists('testClear:test'), [1, true], true));
        $cache->clear();
        $this->assertTrue(in_array($redis->exists('testClear:test'), [0, false], true));
        $this->assertTrue(in_array($redis->exists('redis'), [1, true], true));
    }

    public function testClearWithTag(): void
    {
        $redis = new Redis();
        $redis->connect('redis');
        $redis->select(15);
        $redis->flushDB();
        $this->assertTrue(in_array($redis->exists('test'), [0, false], true));
        $redis->set('redis', 'redis');
        $this->assertTrue(in_array($redis->exists('redis'), [1, true], true));

        $cache = new RedisCache(['host' => 'redis', 'prefix' => 'testClearWithTag', 'database' => 15]);
        $cache = $cache->tag(['test1']);
        $cache->set('test', '1234');
        $this->assertTrue(in_array($redis->exists('testClearWithTag:tag:test1'), [1, true], true));
        $this->assertTrue(in_array($redis->exists('testClearWithTag:test'), [1, true], true));
        $cache->clear();
        $this->assertTrue(in_array($redis->exists('testClearWithTag:tag:test1'), [0, false], true));
        $this->assertTrue(in_array($redis->exists('testClearWithTag:test'), [0, false], true));
        $this->assertTrue(in_array($redis->exists('redis'), [1, true], true));
    }

}
