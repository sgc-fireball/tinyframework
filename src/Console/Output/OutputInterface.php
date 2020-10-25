<?php declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

interface OutputInterface
{

    public function __construct(Color $color = null);

    public function ansi(bool $ansi = null);

    public function write(string $text);

    public function writeln(string $text);

    public function box(string $text, string $start = '', string $end = '');

    public function error(string $text): void;

    public function warning(string $text): void;

    public function info(string $text): void;

    public function successful(string $text): void;

}
