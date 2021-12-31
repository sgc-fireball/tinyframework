<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Core;

use DateTime;
use PHPUnit\Framework\TestCase;
use TinyFramework\Core\Config;

class ConfigTest extends TestCase
{
    private ?Config $config;

    public function setUp(): void
    {
        $this->config = (new \ReflectionClass(Config::class))->newInstanceWithoutConstructor();
    }

    public function testEmpty(): void
    {
        $this->assertIsArray($this->config->get());
        $this->assertTrue(empty($this->config->get()));
    }

    public function testGetSet(): void
    {
        $this->assertIsArray($this->config->get());
        $this->assertTrue(empty($this->config->get()));
        $this->config->set('module.test', 'test1');
        $this->assertEquals('test1', $this->config->get('module.test'));
        $this->config->set('module.test.test2', 'test2');
        $this->assertEquals('test2', $this->config->get('module.test.test2'));
        $this->assertEquals(['test2' => 'test2'], $this->config->get('module.test'));
    }

    public function testLoadAndGet(): void
    {
        $this->assertIsArray($this->config->get());
        $this->assertTrue(empty($this->config->get()));
        $this->config->load('test', __DIR__ . '/inc/config.php');
        $this->assertEquals('config1', $this->config->get('test.config1'));
        $this->assertEquals('config3', $this->config->get('test.config2.config3'));
    }
}
