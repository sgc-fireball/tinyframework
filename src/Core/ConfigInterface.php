<?php

declare(strict_types=1);

namespace TinyFramework\Core;

interface ConfigInterface
{
    public function __construct(#[\SensitiveParameter] array $config);

    public function load(string $name, string $file): ConfigInterface;

    public function get(string $key = null): mixed;

    public function set(string $key, mixed $value): ConfigInterface;
}
