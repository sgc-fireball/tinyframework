<?php declare(strict_types=1);

namespace TinyFramework\Color;

interface NameConverterInterface
{

    public function name2hex(string $name): ?string;

    public function hex2name(string $hexIn): string;

}
