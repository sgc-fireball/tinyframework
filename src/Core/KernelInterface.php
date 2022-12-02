<?php

declare(strict_types=1);

namespace TinyFramework\Core;

interface KernelInterface
{
    public function __construct(ContainerInterface $container);

    public function runningInConsole(): bool;

    public function inMaintenanceMode(): bool;

    public function getMaintenanceConfig(): array|null;

    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool;

    public function handleException(\Throwable $e): int;

    public function handleShutdown(): void;
}
