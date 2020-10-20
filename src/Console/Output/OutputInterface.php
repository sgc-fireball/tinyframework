<?php declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

interface OutputInterface
{

    public function __construct(Color $color = null);

    public function ansi(bool $ansi = null);

    public function write(string $text);

    public function writeln(string $text);

}
