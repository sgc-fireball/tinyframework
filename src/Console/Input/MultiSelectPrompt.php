<?php

namespace TinyFramework\Console\Input;

class MultiSelectPrompt extends SelectPrompt
{

    public function prepare(): void
    {

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

}
