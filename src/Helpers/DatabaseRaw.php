<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

class DatabaseRaw implements \Stringable
{
    protected string $value = '';

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function string(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
