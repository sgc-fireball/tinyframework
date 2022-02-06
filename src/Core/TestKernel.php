<?php

namespace TinyFramework\Core;

class TestKernel extends Kernel
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->container->alias('kernel', TestKernel::class)
            ->alias(Kernel::class, TestKernel::class)
            ->singleton(TestKernel::class, $this);
        $this->findServiceProviders();
        $this->register();
        $this->boot();
    }

    public function handleException(\Throwable $e): int
    {
        self::$reservedMemory = null; // free 10kb ram
        // need to defined!
        throw $e;
    }
}
