<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Session;

use PHPUnit\Framework\TestCase;
use TinyFramework\Session\FileSession;
use TinyFramework\Session\SessionInterface;

class FileSessionTest extends TestCase
{
    private ?SessionInterface $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = new FileSession([
            'path' => sys_get_temp_dir(),
            'ttl' => 300,
        ]);
    }

    public function testInMemory(): void
    {
        $this->handler->open();
        $this->assertFalse($this->handler->has('test'));
        $this->handler->set('test', 1);
        $this->assertTrue($this->handler->has('test'));
        $this->handler->destroy();
    }

    public function testOpenAndWrite(): void
    {
        $this->handler->open($guid = guid());
        $this->assertFalse($this->handler->has('test'));
        $this->handler->set('test', 1);
        $this->assertTrue($this->handler->has('test'));
        $this->handler->close();

        $this->handler->open($guid);
        $this->assertTrue($this->handler->has('test'));
        $this->handler->destroy();

        $this->handler->open($guid);
        $this->assertFalse($this->handler->has('test'));
    }
}
