<?php declare(strict_types=1);

namespace TinyFramework\Core;

use RuntimeException;

class Config implements ConfigInterface
{

    private array $config = [];

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
        // @TODO implement config cache
        $this->loadFolder(__DIR__ . '/config');
        $this->loadFolder(root_dir() . '/config');
    }

    private function loadFolder(string $path): static
    {
        if (is_dir($path)) {
            foreach (glob($path . '/*.php') as $file) {
                $this->load(str_replace('.php', '', basename($file)), $file);
            }
        }
        return $this;
    }

    public function load(string $name, string $file): static
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new RuntimeException('Could not load config.');
        }
        $this->config[$name] = $this->merge(
            $this->config[$name] ?? [],
            (array)require($file)
        );
        return $this;
    }

    private function merge(array $output = [], array $input = []): array
    {
        foreach ($input as $key => $value) {
            if (is_numeric($key)) {
                $output[] = $value;
            } else if (array_key_exists($key, $output) && is_array($output[$key]) && is_array($input[$key])) {
                $output[$key] = $this->merge($output[$key], $value);
            } else {
                $output[$key] = $value;
            }
        }
        return $output;
    }

    public function get(string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->config;
        }
        if (mb_strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $config = $this->config;
            foreach ($keys as $key) {
                if (!array_key_exists($key, $config)) {
                    return null;
                }
                $config = $config[$key];
            }
            return $config;
        }
        return array_key_exists($key, $this->config) ? $this->config[$key] : null;
    }

    public function set(string $key, mixed $value): static
    {
        $keys = strpos($key, '.') === false ? [$key] : explode('.', $key);
        $key = $keys[count($keys) - 1];
        $config = &$this->config;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $config) || !is_array($config[$key])) {
                $config[$key] = [];
            }
            $config = &$config[$key];
        }
        $config[$key] = $value;
        return $this;
    }

}
