<?php

namespace TinyFramework\Console\Input;

use TinyFramework\Shell\Terminal;

class SelectPrompt extends AbstractPrompt
{

    protected int $index = 0;
    protected array $keys = [];

    /**
     * @var array<int|string, string>
     */
    protected array $answers = [];

    protected int $showCount = 10;
    protected int $printLines = 0;

    public function prepare(): void
    {
        $this->keys = array_keys($this->answers);
        // calc the number of lines, that will be really displayed
        $this->printLines = min(count($this->keys), $this->showCount);

        $this->on(Terminal::ESCAPE, function (): bool {
            $this->index = 0;
            return true;
        });
        $this->on(Terminal::ENTER, function (): bool {
            return false;
        });
        $this->on(Terminal::CTRL_C, function(): bool {
            $this->result = null;
            return false;
        });
        $this->on([Terminal::LEFT, Terminal::LEFT_ARROW, Terminal::UP, Terminal::UP_ARROW], function(): bool {
            $this->index = max(0, $this->index - 1);
            return true;
        });
        $this->on(Terminal::PAGE_UP, function(): bool {
            $this->index = max(0, $this->index - 10);
            return true;
        });
        $this->on([Terminal::RIGHT, Terminal::RIGHT_ARROW, Terminal::DOWN, Terminal::DOWN_ARROW], function(): bool {
            $this->index = min($this->index + 1, count($this->answers) - 1);
            return true;
        });
        $this->on(Terminal::PAGE_DOWN, function(): bool {
            $this->index = min($this->index + 10, count($this->answers) - 1);
            return true;
        });
    }

    public function render(): void
    {
        $startIndex = max(0, $this->index - ($this->showCount / 2));
        $startIndex = min($startIndex, count($this->answers) - $this->showCount);
        $showOptions = array_slice($this->answers, $startIndex, $this->printLines);

        $this->terminal->eraseLines($this->printLines);
        foreach ($showOptions as $key => $value) {
            $this->output->writeln(
                sprintf(
                    '%s%s%s',
                    $key !== $this->keys[$this->index] ? '<lightblue>' : '',
                    $value,
                    $key !== $this->keys[$this->index] ? '</lightblue>' : ''
                )
            );
        }
        $this->terminal->moveCursorUp($this->printLines);
    }

    protected function getResult(): int|string|null
    {
        return $this->result;
    }

    public function cleanup(): void
    {
        $this->terminal->eraseLines($this->printLines);
    }

}
