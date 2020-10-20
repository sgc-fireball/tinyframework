<?php declare(strict_types=1);

namespace TinyFramework\Console\Output;

use TinyFramework\Color\Color;

class Output implements OutputInterface
{

    private Color $color;

    private bool $ansi = true;

    private array $ansiCommands = [
        'bold' => ["\e[1m", "\e[21m"],
        'dim' => ["\e[2m", "\e[22m"],
        'underline' => ["\e[4m", "\e[24m"],
        'blink' => ["\e[5m", "\e[25m"],
        'hide' => ["\e[8m", "\e[28m"],

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
        $this->ansi = array_key_exists('TERM', $_SERVER) && $_SERVER['TERM'] === 'xterm';
    }

    public function ansi(bool $ansi = null)
    {
        if (is_null($ansi)) {
            return $this->ansi;
        }
        $this->ansi = $ansi;
        return $this;
    }

    /**
     * @see https://misc.flogisoft.com/bash/tip_colors_and_formatting
     */
    public function write(string $text)
    {
        $text = preg_replace_callback('/<([#a-z0-9:]+)>(.*)<\/([#a-z0-9:]+)>/m', function (array $matches) {
            if (count($matches) !== 4) {
                return $matches[0];
            }
            [$text, $tag,] = $matches;
            $command = $tag;
            $start = '';
            $end = '';
            if ($this->ansi) {
                $end = "\e[0m";
                if (array_key_exists($command, $this->ansiCommands)) {
                    [$start, $end] = $this->ansiCommands[$command];
                } else {
                    $value = $command;
                    if (strpos($command, ':') !== false) {
                        [$command, $value] = explode(':', $command);
                    }
                    $xterm = $this->color->name2xterm($value);
                    $xterm = is_null($xterm) && preg_match('/^#[a-z0-9]{6}$/', $value) ? $this->color->hex2xterm($value) : $xterm;
                    if ($xterm) {
                        if ($command === 'bg') {
                            $start = sprintf("\e[48;5;%dm", $xterm);
                        } else if ($command === 'fg' || $command === $value) {
                            $start = sprintf("\e[38;5;%dm", $xterm);
                        }
                    }
                }
            }
            $text = str_replace('<' . $tag . '>', $start, $text);
            $text = str_replace('</' . $tag . '>', $end, $text);
            return $text;
        }, $text);
        echo $text;
        flush();
    }

    public function writeln(string $text = '')
    {
        $this->write($text . PHP_EOL);
    }

}
