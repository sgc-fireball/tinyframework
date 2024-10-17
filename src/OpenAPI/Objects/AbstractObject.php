<?php

namespace TinyFramework\OpenAPI\Objects;

abstract class AbstractObject
{

    public array $extensions = [];

    abstract public static function parse(array $arr): AbstractObject;

    protected function parseExtension(array $arr): self
    {
        foreach ($arr as $key => $value) {
            if (!str_starts_with($key, 'x-') || str_starts_with($key, 'x-oai') || str_starts_with($key, 'x-oas')) {
                continue;
            }
            $name = substr($key, 2);
            $this->extensions[$name] = $value;
        }
        return $this;
    }

    public function __get(string $name): mixed
    {
        if (!str_starts_with($name, 'x-') || str_starts_with($name, 'x-oai') || str_starts_with($name, 'x-oas')) {
            throw new \RuntimeException('Field ' . $name . ' does not exists here.');
        }
        $realName = substr($name, 2);
        if (!array_key_exists($realName, $this->extensions)) {
            throw new \RuntimeException('Field ' . $name . ' does not exists here.');
        }
        return $this->extensions[$realName];
    }

}
