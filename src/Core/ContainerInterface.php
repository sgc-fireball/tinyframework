<?php

declare(strict_types=1);

namespace TinyFramework\Core;

interface ContainerInterface
{
    public static function instance(): ContainerInterface;

    public function tag(string|array $tags, string|array $instances): ContainerInterface;

    public function tagged(string $tag): array;

    public function has(string $key): bool;

    public function get(string $key): mixed;

    public function resolveAlias(string|array|callable|object $key): string|array|callable|object;

    public function singleton(string $key, string|array|callable|object $object): ContainerInterface;

    public function alias(string $alias, string $key): ContainerInterface;

    public function call(string|array|callable|object $callable, array $parameters = []): mixed;
}
