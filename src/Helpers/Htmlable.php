<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

class Htmlable extends Str
{
    public function html(string $value = null): static|string
    {
        if ($value !== null) {
            $this->value = $value;
        }
        return $this;
    }
}
