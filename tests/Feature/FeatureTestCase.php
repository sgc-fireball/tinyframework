<?php declare(strict_types=1);

namespace TinyFramework\Tests\Feature;

use PHPUnit\Framework\TestCase;
use TinyFramework\Core\Container;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Core\TestKernel;

class FeatureTestCase extends TestCase
{

    protected ?Container $container;

    protected ?KernelInterface $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);
        $this->kernel = $this->container->get(TestKernel::class);
    }

    protected function tearDown(): void
    {
        unset($this->container);
        gc_collect_cycles();
        parent::tearDown();
    }

}
