<?php declare(strict_types=1);

namespace TinyFramework\Core;

use RuntimeException;

interface ConfigInterface
{

    public function __construct(array $config);

    public function load(string $name, string $file): ConfigInterface;

    /**
     * @param string|null $key
     * @param null $default
     * @return array|mixed|null
     */
    public function get(string $key = null, $default = null);

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set(string $key, $value): ConfigInterface;

}
