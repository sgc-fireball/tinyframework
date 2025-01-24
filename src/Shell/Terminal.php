<?php

declare(strict_types=1);

namespace TinyFramework\Shell;

class Terminal
{

    /**
     * @link https://github.com/laravel/prompts/blob/main/src/Key.php#L48
     */
    public const PAGE_UP = "\e[5~";
    public const PAGE_DOWN = "\e[6~";
    public const UP = "\e[A";
    public const SHIFT_UP = "\e[1;2A";
    public const DOWN = "\e[B";
    public const SHIFT_DOWN = "\e[1;2B";
    public const RIGHT = "\e[C";
    public const LEFT = "\e[D";
    public const UP_ARROW = "\eOA";
    public const DOWN_ARROW = "\eOB";
    public const RIGHT_ARROW = "\eOC";
    public const LEFT_ARROW = "\eOD";
    public const ESCAPE = "\e";
    public const DELETE = "\e[3~";
    public const BACKSPACE = "\177";
    public const ENTER = "\n";
    public const SPACE = ' ';
    public const TAB = "\t";
    public const SHIFT_TAB = "\e[Z";
    public const HOME = ["\e[1~", "\eOH", "\e[H", "\e[7~"];
    public const END = ["\e[4~", "\eOF", "\e[F", "\e[8~"];

    /**
     * Cancel/SIGINT
     */
    public const CTRL_C = "\x03";

    /**
     * Previous/Up
     */
    public const CTRL_P = "\x10";

    /**
     * Next/Down
     */
    public const CTRL_N = "\x0E";

    /**
     * Forward/Right
     */
    public const CTRL_F = "\x06";

    /**
     * Back/Left
     */
    public const CTRL_B = "\x02";

    /**
     * Backspace
     */
    public const CTRL_H = "\x08";

    /**
     * Home
     */
    public const CTRL_A = "\x01";

    /**
     * EOF
     */
    public const CTRL_D = "\x04";

    /**
     * End
     */
    public const CTRL_E = "\x05";

    /**
     * Negative affirmation
     */
    public const CTRL_U = "\x15";

    private static ?string $initialSttyConfig = null;

    private static bool $cursorHidden = false;

    public function setTtyConfig(string $options): void
    {
        if (!self::$initialSttyConfig) {
            exec('stty -g', $output);
            self::$initialSttyConfig = implode("\n", $output);
        }
        exec(sprintf('stty %s', $options));
    }

    public function restoreTtyConfig(): void
    {
        if (self::$initialSttyConfig) {
            exec(sprintf('stty %s', self::$initialSttyConfig));
            self::$initialSttyConfig = null;
        }
    }

    public function hideCursor(): void
    {
        fwrite(STDOUT, "\e[?25l");
        self::$cursorHidden = true;
    }

    public function showCursor(): void
    {
        fwrite(STDOUT, "\e[?25h");
        self::$cursorHidden = false;
    }

    public function restoreCursor(): void
    {
        if (self::$cursorHidden) {
            $this->showCursor();
        }
    }

    public function moveCursor(int $x, int $y = 0): void
    {
        $y < 0 ? $this->moveCursorLeft(abs($x)) : $this->moveCursorRight($x);
        $y < 0 ? $this->moveCursorUp(abs($y)) : $this->moveCursorDown($y);
    }

    public function moveCursorToColumn(int $column): void
    {
        fwrite(STDOUT, "\e[{$column}G");
    }

    public function moveCursorLeft(int $lines): void
    {
        fwrite(STDOUT, "\e[{$lines}D");
    }

    public function moveCursorRight(int $count): void
    {
        if ($count > 0) {
            fwrite(STDOUT, "\e[{$count}C");
        }
    }

    public function moveCursorUp(int $lines): void
    {
        fwrite(STDOUT, "\e[{$lines}A");
    }

    public function moveCursorDown(int $lines): void
    {
        fwrite(STDOUT, "\e[{$lines}B");
    }

    public function eraseLines(int $count): void
    {
        $clear = "\r";
        for ($i = 1; $i <= $count; $i++) {
            $clear .= "\e[K\n";
        }
        if ($count) {
            $clear .= "\e[{$count}A";
        }
        fwrite(STDOUT, $clear);
    }

    public function eraseDown(): void
    {
        fwrite(STDOUT, "\e[J");
    }

    public function read(): string
    {
        $input = fread(STDIN, 1024);
        return $input === false ? '' : $input;
    }

    public function exit(int $code): never
    {
        exit(max(0, min($code, 255)));
    }

    public function exec(string $command, int &$code): string
    {
        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (!$process) {
            throw new \RuntimeException('Failed to create process.');
        }

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        $code = proc_close($process);

        if ($code !== 0 || $stdout === false) {
            throw new \RuntimeException(trim($stderr ?: "Unknown error (code: $code)"), $code);
        }

        return $stdout;
    }

    public function width(): int
    {
        if (command_exists('stty')) {
            $output = @exec('stty size 2>/dev/null');
            if (preg_match('/\d+ \d+/', $output)) {
                [, $width] = explode(' ', $output);
                return (int)trim($width);
            }
        }

        if (command_exists('tput')) {
            return (int)trim(@shell_exec('tput cols'));
        }

        return 80;
    }

    public function height(): int
    {
        if (command_exists('stty')) {
            $output = @exec('stty size 2>/dev/null');
            if (preg_match('/\d+ \d+/', $output)) {
                [$height,] = explode(' ', $output);
                return (int)trim($height);
            }
        }

        if (command_exists('tput')) {
            return (int)trim(@shell_exec('tput lines'));
        }

        return 25;
    }

    public function __destruct()
    {
        $this->restoreCursor();
        $this->restoreTtyConfig();
    }

}
