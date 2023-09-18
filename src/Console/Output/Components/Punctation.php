<?php

declare(strict_types=1);

namespace TinyFramework\Console\Output\Components;

use TinyFramework\Console\Output\OutputInterface;

class Punctation
{
    private OutputInterface $output;
    private string $titleStyle = '<fg:darkgray>%s</fg>';
    private string $valueStyle = '<fg:yellow>%s</fg>';
    private ?string $title = null;
    private ?string $value = null;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function title(?string $title = null): self
    {
        $this->title = $title;
        return $this;
    }

    public function value(mixed $value): self
    {
        if (is_object($value)) {
            $this->value = get_class($value);
        } elseif (is_array($value)) {
            $this->value = (string)count($value);
        } elseif (is_numeric($value)) {
            $this->value = (string)$value;
        } elseif (is_bool($value)) {
            $this->value = $value ? 'true' : 'false';
        } else {
            $this->value = (string)$value;
        }
        return $this;
    }

    public function display(): self
    {
        $width = $this->output->width();
        $title = $this->title;
        $title .= mb_strlen($title) % 2 === 0 ? '' : ' ';

        $value = $this->value;
        $value = (mb_strlen($value) % 2 === 0 ? '' : ' ') . $value;

        $width -= (mb_strlen($title) % $width);
        $width -= (mb_strlen($value) % $width);
        $width = (int)ceil($width / 2) - 2;
        if ($width > 0) {
            $middle = str_repeat('. ', $width);
            $middle .= mb_strlen($middle) % 2 == 0 ? '' : ' ';
            $this->output->writeln(
                sprintf(
                    "%s %s %s\n",
                    sprintf($this->titleStyle, $title),
                    $middle,
                    sprintf($this->valueStyle, $value)
                )
            );
        } else {
            $width = $this->output->width();
            $width -= (mb_strlen($title) % $width);
            $width = (int)ceil($width / 2) - 2;
            $middle = $width > 0 ? str_repeat('. ', $width) : '';
            $middle .= mb_strlen($middle) % 2 == 0 ? '' : ' ';
            $this->output->writeln(sprintf($this->titleStyle, $title) . ' ' . $middle);

            $width = $this->output->width();
            $width -= (mb_strlen($value) % $width);
            $width = (int)ceil($width / 2) - 2;
            $middle = $width > 0 ? str_repeat(' .', $width) : '';
            $middle .= mb_strlen($middle) % 2 == 0 ? ' ' : '';
            $this->output->writeln($middle . ' ' . sprintf($this->valueStyle, $value) . "\n");
        }

        return $this;
    }

    public function titleStyle(string $style): self
    {
        if (strpos($style, '%s') === false) {
            throw new \InvalidArgumentException('Missing placeholder %s');
        }
        $this->titleStyle = $style;
        return $this;
    }

    public function valueStyle(string $style): self
    {
        if (strpos($style, '%s') === false) {
            throw new \InvalidArgumentException('Missing placeholder %s');
        }
        $this->valueStyle = $style;
        return $this;
    }
}
