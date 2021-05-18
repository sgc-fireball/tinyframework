<?php declare(strict_types=1);

namespace TinyFramework\Helpers;

class Htmlable extends Str
{

    public function html(string $value = null): static
    {
        if (!is_null($value)) {
            $this->value = $value;
        }
        return $this;
    }

}
