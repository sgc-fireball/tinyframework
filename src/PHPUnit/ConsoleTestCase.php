<?php

declare(strict_types=1);

namespace TinyFramework\PHPUnit;

use PHPUnit\Framework\TestCase;
use TinyFramework\Core\Container;
use TinyFramework\Core\TestConsoleKernel;

abstract class ConsoleTestCase extends TestCase
{
    protected ?Container $container;

    protected ?TestConsoleKernel $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::instance();
        $this->kernel = $this->container->get(TestConsoleKernel::class);
    }

    protected function tearDown(): void
    {
        unset($this->container);
        unset($this->kernel);
        gc_collect_cycles();
        parent::tearDown();
    }
}
