<?php declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

interface OutputInterface
{

    const VERBOSITY_QUIET = -1;
    const VERBOSITY_NORMAL = 0;
    const VERBOSITY_VERBOSE = 1;
    const VERBOSITY_VERY_VERBOSE = 2;
    const VERBOSITY_DEBUG = 3;

    public function __construct(Color $color = null);

    public function ansi(bool $ansi = null): OutputInterface|bool;

    public function quiet(bool $quiet = null): OutputInterface|bool;

    public function verbosity(int $verbosity = null): OutputInterface|int;

    public function write(string $text): void;

    public function writeln(string $text): void;

    public function box(string $text, string $start = '', string $end = ''): void;

    public function error(string $text): void;

    public function warning(string $text): void;

    public function info(string $text): void;

    public function successful(string $text): void;

    public function width(): int;

    public function height(): int;

}
