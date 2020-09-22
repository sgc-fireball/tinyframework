<?php declare(strict_types=1);

namespace TinyFramework\Core;

interface ContainerInterface
{

    public static function instance(): ContainerInterface;

    public function tag($tags, $instances): ContainerInterface;

    public function tagged(string $tag): array;

    public function has(string $key): bool;

    public function get(string $key);

    /**
     * @param mixed|string $key
     * @return mixed|string
     */
    public function resolveAlias($key);

    public function singleton(string $key, $object);

    public function alias(string $alias, string $key): ContainerInterface;

    public function call($callable, array $parameters = []);

}
