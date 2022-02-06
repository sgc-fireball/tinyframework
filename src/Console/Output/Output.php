<?php

declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

class Output implements OutputInterface
{
    private const FOREGROUND_COLOR_RESET = "\e[39m";
    private const BACKGROUND_COLOR_RESET = "\e[49m";

    private Color $color;

    private bool $ansi;

    private int $verbosity = 0;

    private int|null $width = null;

    private int|null $height = null;

    /**
     * foreground;background;attributes
     * @see https://misc.flogisoft.com/bash/tip_colors_and_formatting
     * @see https://github.com/symfony/console/blob/b61edc965ce547319f0b9dad02ba22c96da27c5a/Color.php#L143
     * @see https://github.com/symfony/console/blob/5.x/Formatter/OutputFormatter.php
     * reset attributes: \e[0m
     * background-RGB: \e[48;2;R;G;Bm
     * foreground-RGB: \e[38;2;R;G;Bm
     */
    private array $ansiCommands = [
        'bold' => ["\e[1m", "\e[22m"],
        'dim' => ["\e[2m", "\e[22m"],
        #'3' => ["\e[3m", "\e[23m"],
        'underline' => ["\e[4m", "\e[24m"],
        #'blink' => ["\e[5m", "\e[25m"],
        #'6' => ["\e[6m", "\e[26m"], // gray invert
        #'invert' => ["\e[7m", "\e[27m"],
        #'hidden' => ["\e[8m", "\e[28m"],

        'black' => ["\e[30m", self::FOREGROUND_COLOR_RESET],
        'red' => ["\e[31m", self::FOREGROUND_COLOR_RESET],
        'green' => ["\e[32m", self::FOREGROUND_COLOR_RESET],
        'yellow' => ["\e[33m", self::FOREGROUND_COLOR_RESET],
        'blue' => ["\e[34m", self::FOREGROUND_COLOR_RESET],
        'magenta' => ["\e[35m", self::FOREGROUND_COLOR_RESET],
        'cyan' => ["\e[36m", self::FOREGROUND_COLOR_RESET],
        'lightgray' => ["\e[37m", self::FOREGROUND_COLOR_RESET],
        'darkgray' => ["\e[90m", self::FOREGROUND_COLOR_RESET],
        'lightred' => ["\e[91m", self::FOREGROUND_COLOR_RESET],
        'lightgreen' => ["\e[92m", self::FOREGROUND_COLOR_RESET],
        'lightyellow' => ["\e[93m", self::FOREGROUND_COLOR_RESET],
        'lightblue' => ["\e[94m", self::FOREGROUND_COLOR_RESET],
        'lightmagenta' => ["\e[95m", self::FOREGROUND_COLOR_RESET],
        'lightcyan' => ["\e[96m", self::FOREGROUND_COLOR_RESET],
        'white' => ["\e[97m", self::FOREGROUND_COLOR_RESET],

        'bg:black' => ["\e[40m", self::BACKGROUND_COLOR_RESET],
        'bg:red' => ["\e[41m", self::BACKGROUND_COLOR_RESET],
        'bg:green' => ["\e[42m", self::BACKGROUND_COLOR_RESET],
        'bg:yellow' => ["\e[43m", self::BACKGROUND_COLOR_RESET],
        'bg:blue' => ["\e[44m", self::BACKGROUND_COLOR_RESET],
        'bg:magenta' => ["\e[45m", self::BACKGROUND_COLOR_RESET],
        'bg:cyan' => ["\e[46m", self::BACKGROUND_COLOR_RESET],
        'bg:lightgray' => ["\e[47m", self::BACKGROUND_COLOR_RESET],
        'bg:darkgray' => ["\e[100m", self::BACKGROUND_COLOR_RESET],
        'bg:lightred' => ["\e[101m", self::BACKGROUND_COLOR_RESET],
        'bg:lightgreen' => ["\e[102m", self::BACKGROUND_COLOR_RESET],
        'bg:lightyellow' => ["\e[103m", self::BACKGROUND_COLOR_RESET],
        'bg:lightblue' => ["\e[104m", self::BACKGROUND_COLOR_RESET],
        'bg:lightmagenta' => ["\e[105m", self::BACKGROUND_COLOR_RESET],
        'bg:lightcyan' => ["\e[106m", self::BACKGROUND_COLOR_RESET],
        'bg:white' => ["\e[107m", self::BACKGROUND_COLOR_RESET],
    ];

    public function __construct(Color $color = null)
    {
        $this->color = $color ?? new Color();
        $this->ansi = false;
        if (array_key_exists('TERM', $_SERVER)) {
            $this->ansi = in_array($_SERVER['TERM'], [
                'xterm',
                'xterm-256color'
            ]);
        }
        $this->onResize();
    }

    protected function onResize(): void
    {
        $command = trim(`which stty`);
        if ($command && is_executable($command)) {
            $output = @exec($command . ' size 2>/dev/null');
            if (preg_match('/\d+ \d+/', $output)) {
                list($height, $width) = explode(' ', $output);
                $this->height = (int)trim($height);
                $this->width = (int)trim($width);
            }
        }
        if ($this->width === null && $this->height === null && isset($_SERVER['TERM'])) {
            $command = trim(`which tput`);
            if ($command && is_executable($command)) {
                $this->width = (int)trim(@shell_exec($command . ' cols'));
                $this->height = (int)trim(@shell_exec($command . ' lines'));
            }
        }
        if ($this->width === null && $this->height === null) {
            $this->width = 80;
            $this->height = 25;
        }
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function ansi(bool $ansi = null): static|bool
    {
        if ($ansi === null) {
            return $this->ansi;
        }
        $this->ansi = $ansi;
        return $this;
    }

    public function quiet(bool $quiet = null): static|bool
    {
        if ($quiet === null) {
            return $this->verbosity === -1;
        }
        $this->verbosity = -1;
        return $this;
    }

    public function verbosity(int $verbosity = null): static|int
    {
        if ($verbosity === null) {
            return max(-1, min($this->verbosity, 3));
        }
        $this->verbosity = $verbosity;
        return $this;
    }

    public function write(string $text): void
    {
        if ($this->verbosity === -1) {
            return;
        }
        preg_match_all('/<(\/)?([a-zA-Z0-9#:]+)>/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $replace = '';
            list($match, $end, $command) = $match;
            if ($this->ansi) {
                $replace = "\e[0m";
                if (array_key_exists($command, $this->ansiCommands)) {
                    $replace = $this->ansiCommands[$command][$end ? 1 : 0];
                } else {
                    $value = $command;
                    if (strpos($command, ':') !== false) {
                        [$command, $value] = explode(':', $command);
                    }
                    $xterm = $this->color->name2xterm($value);
                    $xterm = $xterm === null && preg_match('/^#[a-z0-9]{6}$/', $value) ? $this->color->hex2xterm($value) : $xterm;
                    if ($xterm) {
                        if ($command === 'bg') {
                            $replace = $end ? "\e[49m" : sprintf("\e[48;5;%dm", $xterm);
                        } elseif ($command === 'fg' || $command === $value) {
                            $replace = $end ? "\e[39m" : sprintf("\e[38;5;%dm", $xterm);
                        }
                    }
                }
            }
            $text = str_replace($match, $replace, $text);
        }
        echo $text;
        flush();
    }

    public function writeln(string $text = ''): void
    {
        $this->write($text . PHP_EOL);
    }

    public function box(string $text, string $start = '', string $end = ''): void
    {
        $message = str_pad(' ', $this->width) . PHP_EOL;
        foreach (explode("\n", wordwrap($text, $this->width - 4, "\n", true)) as $text) {
            $message .= '  ' . str_pad(trim($text), $this->width - 4) . '  ' . PHP_EOL;
        }
        $message .= str_pad(' ', $this->width);
        $this->writeln($start . $message . $end);
    }

    public function error(string $text): void
    {
        $this->box($text, '<bg:red><white>', '</white></bg:red>');
    }

    public function warning(string $text): void
    {
        $this->box($text, '<bg:yellow><black>', '</black></bg:yellow>');
    }

    public function info(string $text): void
    {
        $this->box($text, '<bg:blue><white>', '</white></bg:blue>');
    }

    public function successful(string $text): void
    {
        $this->box($text, '<bg:green><black>', '</black></bg:green>');
    }
}
