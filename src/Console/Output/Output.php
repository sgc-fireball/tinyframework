<?php declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

class Output implements OutputInterface
{

    private Color $color;

    private bool $ansi;

    private int $verbosity = 0;

    private int $width = 80;

    private int $height = 25;

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

        'black' => ["\e[30m", "\e[39m"],
        'red' => ["\e[31m", "\e[39m"],
        'green' => ["\e[32m", "\e[39m"],
        'yellow' => ["\e[33m", "\e[39m"],
        'blue' => ["\e[34m", "\e[39m"],
        'magenta' => ["\e[35m", "\e[39m"],
        'cyan' => ["\e[36m", "\e[39m"],
        'lightgray' => ["\e[37m", "\e[39m"],
        'darkgray' => ["\e[90m", "\e[39m"],
        'lightred' => ["\e[91m", "\e[39m"],
        'lightgreen' => ["\e[92m", "\e[39m"],
        'lightyellow' => ["\e[93m", "\e[39m"],
        'lightblue' => ["\e[94m", "\e[39m"],
        'lightmagenta' => ["\e[95m", "\e[39m"],
        'lightcyan' => ["\e[96m", "\e[39m"],
        'white' => ["\e[97m", "\e[39m"],

        'bg:black' => ["\e[40m", "\e[49m"],
        'bg:red' => ["\e[41m", "\e[49m"],
        'bg:green' => ["\e[42m", "\e[49m"],
        'bg:yellow' => ["\e[43m", "\e[49m"],
        'bg:blue' => ["\e[44m", "\e[49m"],
        'bg:magenta' => ["\e[45m", "\e[49m"],
        'bg:cyan' => ["\e[46m", "\e[49m"],
        'bg:lightgray' => ["\e[47m", "\e[49m"],
        'bg:darkgray' => ["\e[100m", "\e[49m"],
        'bg:lightred' => ["\e[101m", "\e[49m"],
        'bg:lightgreen' => ["\e[102m", "\e[49m"],
        'bg:lightyellow' => ["\e[103m", "\e[49m"],
        'bg:lightblue' => ["\e[104m", "\e[49m"],
        'bg:lightmagenta' => ["\e[105m", "\e[49m"],
        'bg:lightcyan' => ["\e[106m", "\e[49m"],
        'bg:white' => ["\e[107m", "\e[49m"],
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
        $width = 80;
        $height = 50;
        $command = trim(`which stty`);
        if (is_executable($command)) {
            list($height, $width) = explode(' ', @exec($command . ' size 2>/dev/null') ?: $width . ' ' . $height);
        }
        $command = trim(`which tput`);
        if (is_executable($command)) {
            $width = (int)trim(shell_exec($command . ' cols'));
            $height = (int)trim(shell_exec($command . ' lines'));
        }
        $this->width = $width;
        $this->height = $height;
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
        if (is_null($ansi)) {
            return $this->ansi;
        }
        $this->ansi = $ansi;
        return $this;
    }

    public function quiet(bool $quiet = null): static|bool
    {
        if (is_null($quiet)) {
            return $this->verbosity === -1;
        }
        $this->verbosity = -1;
        return $this;
    }

    public function verbosity(int $verbosity = null): static|int
    {
        if (is_null($verbosity)) {
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
            if ($this->ansi) {
                list($match, $end, $command) = $match;
                $replace = "\e[0m";
                if (array_key_exists($command, $this->ansiCommands)) {
                    $replace = $this->ansiCommands[$command][$end ? 1 : 0];
                } else {
                    $value = $command;
                    if (strpos($command, ':') !== false) {
                        [$command, $value] = explode(':', $command);
                    }
                    $xterm = $this->color->name2xterm($value);
                    $xterm = is_null($xterm) && preg_match('/^#[a-z0-9]{6}$/', $value) ? $this->color->hex2xterm($value) : $xterm;
                    if ($xterm) {
                        if ($command === 'bg') {
                            $replace = $end ? "\e[49m" : sprintf("\e[48;5;%dm", $xterm);
                        } else if ($command === 'fg' || $command === $value) {
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
