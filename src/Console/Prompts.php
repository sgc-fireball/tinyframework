<?php

declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Shell\Terminal;

class Prompts
{

    public function __construct(
        private readonly InputInterface $input,
        private readonly OutputInterface $output,
        private ?Terminal $terminal = null,
    ) {
        $this->terminal ??= new Terminal();
    }

    public function ask(string $question, mixed $default = null): mixed
    {
        if ($this->input->interaction()) {
            $this->output->write($question . ' ');
            return trim(fgets(STDIN)) ?: $default;
        }
        return $default;
    }

    public function secret(string $question, mixed $default = null): mixed
    {
        if ($this->input->interaction()) {
            $this->output->write($question . ' ');
            $this->terminal->setTtyConfig('-echo');
            $password = trim(fgets(STDIN));
            $this->terminal->restoreTtyConfig();
            $this->output->writeln('');
            return trim($password) ?: $default;
        }
        return $default;
    }

    public function confirm(string $question, bool $default = false): bool
    {
        if ($this->input->interaction()) {
            $this->output->write(
                sprintf(
                    '%s [<bold>%s</bold>/%s]: ',
                    $question,
                    $default === false ? 'N' : 'n',
                    $default === true ? 'Y' : 'y',
                )
            );
            return to_bool(trim(fgets(STDIN)));
        }
        return $default;
    }

    public function suggest(string $question, \Closure $callback, ?string $default = null): ?string
    {
        if (!$this->input->interaction()) {
            return $default;
        }

        $this->output->writeln($question);

        // prepare
        $this->terminal->setTtyConfig('-icanon -isig -echo');
        $input = '';
        $index = 0;
        $cursor = 0;

        //render
        do {
            $options = array_values($callback($input));
            if ($index !== max(0, min($index, count($options) - 1))) {
                $index = 0;
            }
            $suggest = $options[$index] ?? '';
            $shortSuggest = substr($suggest, strlen($input));
            $this->terminal->eraseLines(1);
            $this->output->write(
                sprintf(
                    "\r%s<gray>%s</gray>\r",
                    $input,
                    $shortSuggest
                )
            );
            $this->terminal->moveCursorRight($cursor);
            $key = fread(STDIN, 4);
            if ($key === Terminal::ESCAPE) {
                $input = '';
                $cursor = 0;
            } elseif ($key === Terminal::BACKSPACE) {
                $input = substr($input, 0, strlen($input) - 1);
                $cursor--;
            } elseif ($key === Terminal::TAB) {
                $input = $suggest;
                $cursor = strlen($suggest);
            } elseif ($key === Terminal::ENTER) {
                $this->terminal->eraseLines(1);
                $this->output->writeln($input);
                break;
            } elseif ($key === Terminal::CTRL_C) {
                $input = '';
                break;
            } elseif (in_array($key, [Terminal::UP, Terminal::UP_ARROW])) {
                $index = max(0, $index - 1);
            } elseif (in_array($key, [Terminal::DOWN, Terminal::DOWN_ARROW])) {
                $index = min($index + 1, count($options) - 1);
            } elseif (in_array($key, [Terminal::LEFT, Terminal::LEFT_ARROW])) {
                $cursor = max(0, $cursor - 1);
            } elseif (in_array($key, [Terminal::RIGHT, Terminal::RIGHT_ARROW])) {
                $cursor = min($cursor + 1, strlen($input));
            } elseif (!str_starts_with($key, "\e")) {
                $index = 0;
                $input = substr($input, 0, $cursor) . $key . substr($input, $cursor);
                $cursor++;
            }
        } while (true);
        $this->terminal->restoreTtyConfig();
        return $input ?: $default;
    }

    public function select(
        string $question,
        array $answers,
        int|string $defaultIndex
    ): string|int {
        if (!$this->input->interaction()) {
            return $defaultIndex;
        }

        $this->output->writeln($question);
        $this->terminal->setTtyConfig('-icanon -isig -echo');
        $this->terminal->hideCursor();

        // prepare
        $showCount = 10;
        $index = array_search($defaultIndex, array_keys($answers));
        $keys = array_keys($answers);

        // render
        do {
            $startIndex = max(0, $index - ($showCount / 2));
            $startIndex = min($startIndex, count($answers) - $showCount);
            $lines = min(count($answers), $showCount);
            $showOptions = array_slice($answers, $startIndex, $lines);

            $this->terminal->eraseLines($lines);
            foreach ($showOptions as $key => $value) {
                $this->output->writeln(
                    sprintf(
                        '%s%s%s',
                        $key !== $keys[$index] ? '<lightblue>' : '',
                        $value,
                        $key !== $keys[$index] ? '</lightblue>' : ''
                    )
                );
            }
            $this->terminal->moveCursorUp($lines);

            // read next state
            $key = fread(STDIN, 10);
            if ($key === Terminal::ESCAPE) {
                $index = 0;
            } elseif ($key === Terminal::ENTER) {
                break;
            } elseif ($key === Terminal::CTRL_C) {
                $index = array_search($defaultIndex, array_keys($answers));
                break;
            } elseif (in_array($key, [Terminal::LEFT, Terminal::LEFT_ARROW, Terminal::UP, Terminal::UP_ARROW])) {
                $index = max(0, $index - 1);
            } elseif (in_array($key, [Terminal::PAGE_UP])) {
                $index = max(0, $index - 10);
            } elseif (in_array($key, [Terminal::PAGE_DOWN])) {
                $index = min($index + 10, count($answers) - 1);
            } elseif (in_array($key, [Terminal::RIGHT, Terminal::RIGHT_ARROW, Terminal::DOWN, Terminal::DOWN_ARROW])) {
                $index = min($index + 1, count($answers) - 1);
            }
        } while (true);

        $this->terminal->eraseLines($lines);
        $this->terminal->showCursor();
        $this->terminal->restoreTtyConfig();
        return array_keys($answers)[$index];
    }

    /**
     * @return array|string|int|null
     */
    public function multiselect(
        string $question,
        array $answers,
        array $selected = [],
        int|null $max = null
    ): mixed {
        if (!$this->input->interaction()) {
            return $selected;
        }

        $hint = $max !== null && $max > 1 ? ' <gray>(Only '.$max.' allowed.)</gray>' : '';
        $this->output->writeln($question. $hint);
        $this->terminal->setTtyConfig('-icanon -isig -echo');
        $this->terminal->hideCursor();

        // prepare
        $showCount = 10;
        $index = 0;
        $count = count($answers);
        $keys = array_keys($answers);

        // render
        do {
            $startIndex = max(0, $index - ($showCount / 2));
            $startIndex = min($startIndex, $count - $showCount);
            $lines = min(count($answers), $showCount);
            $showOptions = array_slice($answers, $startIndex, $lines);

            $this->terminal->eraseLines($lines);
            foreach ($showOptions as $key => $value) {
                $isSelected = in_array($key, $selected);
                $isCurrent = $key === $keys[$index];
                $color = '<lightblue>';
                $color = $isSelected ? '<green>' : $color;
                $color = $isCurrent ? '' : $color;
                $color = $isSelected && $isCurrent ? '<lightgreen>' : $color;
                /**
                 * @link https://www.compart.com/en/unicode/U+2610 box
                 * @link https://www.compart.com/en/unicode/U+2611 box check
                 * @link https://www.compart.com/en/unicode/U+2BBD box cross
                 */
                $this->output->writeln(
                    sprintf(
                        "%s %s %s%s",
                        $color,
                        $isSelected
                            ? chr(0xE2).chr(0xAE).chr(0xBD)
                            : chr(0xE2).chr(0x98).chr(0x90) ,
                        $value,
                        str_replace('<', '</', $color)
                    )
                );
            }
            $this->terminal->moveCursorUp($lines);

            // read next state
            $key = fread(STDIN, 10);
            if ($key === Terminal::ESCAPE) {
                $index = 0;
            } elseif ($key === Terminal::ENTER) {
                if ($max === 1) {
                    $selected = [$keys[$index]];
                }
                break;
            } elseif ($key === Terminal::SPACE) {
                if (in_array($keys[$index], $selected)) {
                    $selected = array_filter($selected, fn(mixed $val) => $val !== $keys[$index]);
                } else {
                    $selected[] = $keys[$index];
                    if ($max !== null && count($selected) > $max) {
                        $selected = array_slice($selected, 1, $max);
                    }
                }
            } elseif ($key === Terminal::CTRL_C) {
                break;
            } elseif (in_array($key, [Terminal::LEFT, Terminal::LEFT_ARROW, Terminal::UP, Terminal::UP_ARROW])) {
                $index = max(0, $index - 1);
            } elseif (in_array($key, [Terminal::PAGE_UP])) {
                $index = max(0, $index - 10);
            } elseif (in_array($key, [Terminal::PAGE_DOWN])) {
                $index = min($index + 10, count($answers) - 1);
            } elseif (in_array($key, [Terminal::RIGHT, Terminal::RIGHT_ARROW, Terminal::DOWN, Terminal::DOWN_ARROW])) {
                $index = min($index + 1, count($answers) - 1);
            }
        } while (true);

        $this->terminal->eraseLines($lines);
        $this->terminal->showCursor();
        $this->terminal->restoreTtyConfig();
        if ($max === 1) {
            return count($selected) === 1 ? $selected[0] : null;
        }
        return $selected;
    }
}
