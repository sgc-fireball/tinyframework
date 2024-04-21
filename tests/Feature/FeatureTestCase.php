<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Feature;

use PHPUnit\Framework\TestCase;
use TinyFramework\Core\Container;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\DotEnvInterface;
use TinyFramework\Core\KernelInterface;
use TinyFramework\Core\TestKernel;
use TinyFramework\Http\Request;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;

abstract class FeatureTestCase extends TestCase
{
    protected ?Container $container;

    protected ?KernelInterface $kernel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = Container::instance()->singleton(DotEnvInterface::class, DotEnv::class);
        $this->kernel = $this->container->get(TestKernel::class);
    }

    public function request(RequestInterface $request): Response
    {
        $this->container
            ->alias('request', RequestInterface::class)
            ->alias(Request::class, RequestInterface::class)
            ->singleton(RequestInterface::class, $request);
        $route = $this->container->get('router')->resolve($request);
        if (!$route) {
            return Response::new(null, 404);
        }
        $request->route($route);
        return $this->container->call($request->route()->action(), $request->route()->parameter());
    }

    protected function tearDown(): void
    {
        unset($this->container);
        gc_collect_cycles();
        parent::tearDown();
    }
}
