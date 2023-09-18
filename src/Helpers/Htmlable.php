<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

class Htmlable implements \Stringable
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

    public function html(string $value = null): static|string
    {
        if ($value !== null) {
            $this->value = $value;
        }
        return $this;
    }
}
