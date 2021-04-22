<?php declare(strict_types=1);

namespace TinyFramework\Core;

interface ContainerInterface
{

    public static function instance(): ContainerInterface;

    public function tag($tags, $instances): ContainerInterface;

    public function tagged(string $tag): array;

    public function has(string $key): bool;

    /**
     * @param string $key
     * @param array $parameters
     * @return mixed
     */
    public function get(string $key, array $parameters = []);

    public function resolveAlias(string|array|callable|object $key): string|array|callable|object;

    public function singleton(string $key, string|array|callable|object $object): ContainerInterface;

    public function alias(string $alias, string $key): ContainerInterface;

    public function call(string|array|callable|object $callable, array $parameters = []);

}
