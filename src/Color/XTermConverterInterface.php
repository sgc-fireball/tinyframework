<?php

declare(strict_types=1);

namespace TinyFramework\Color;

interface XTermConverterInterface
{
    public function xterm2hex(int $xterm): string;

    public function hex2xterm(string|int $hexIn): int;
}
