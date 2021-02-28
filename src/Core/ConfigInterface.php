<?php declare(strict_types=1);

namespace TinyFramework\Core;

interface ConfigInterface
{

    public function __construct(array $config);

    public function load(string $name, string $file): ConfigInterface;

    public function get(string $key = null);

    public function set(string $key, $value): ConfigInterface;

}
