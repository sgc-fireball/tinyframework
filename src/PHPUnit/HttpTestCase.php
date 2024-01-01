<?php

declare(strict_types=1);

namespace TinyFramework\PHPUnit;

use PHPUnit\Framework\TestCase;
use TinyFramework\Core\Container;
use TinyFramework\Core\TestHttpKernel;
use TinyFramework\Session\SessionInterface;

abstract class HttpTestCase extends TestCase
{
    protected ?Container $container;

    protected ?TestHttpKernel $kernel;

    protected ?SessionInterface $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::instance();
        $this->kernel = $this->container->get(TestHttpKernel::class);
        $this->session = $this->container->get('session')->clear();
    }

    protected function tearDown(): void
    {
        unset($this->container);
        unset($this->kernel);
        unset($this->session);
        gc_collect_cycles();
        parent::tearDown();
    }
}
