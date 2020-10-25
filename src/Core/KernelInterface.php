<?php declare(strict_types=1);

namespace TinyFramework\Core;

interface KernelInterface
{

    public function __construct(ContainerInterface $container);

    public function runningInConsole(): bool;

    public function handleError(int $level, string $message, string $file = '', int $line = 0, array $context = []);

    public function handleException(\Throwable $e);

    public function handleShutdown();

}
