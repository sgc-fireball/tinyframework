<?php declare(strict_types=1);

namespace TinyFramework\Console\Output\Components;

use TinyFramework\Console\Output\OutputInterface;

/**
 * @see https://symfony.com/doc/current/components/console/helpers/table.html
 */
class Table
{

    private OutputInterface $output;

    private ?string $headerTitle = null;

    private ?string $footerTitle = null;

    private array $header = [];

    private int $cols = 0;

    private array $rows = [];

    private array $columnWidths = [];

    private array $format = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function headerTitle(string $title = null): Table|string|null
    {
        if ($title === null) {
            return $this->headerTitle;
        }
        $this->headerTitle = $title;
        return $this;
    }

    public function footerTitle(string $title = null): Table|string|null
    {
        if ($title === null) {
            return $this->footerTitle;
        }
        $this->footerTitle = $title;
        return $this;
    }

    public function header(array $header = null): Table|array
    {
        if ($header === null) {
            return $this->header;
        }
        $this->header = (array)array_values($header);
        $this->cols = count($this->header);
        foreach ($header as $index => $title) {
            $this->columnWidths[$index] = mb_strlen($title);
        }
        return $this;
    }

    public function format(array $format = null): Table|array
    {
        if ($format === null) {
            return $this->format;
        }
        $this->format = (array)array_values($format);
        return $this;
    }

    public function columnWidths(array|string $columnWidths = null): Table|array
    {
        if ($columnWidths === null) {
            return $this->columnWidths;
        }
        if ($columnWidths === 'auto') {
            $totalLength = $this->output->width() - 2;
            $length = (int)(($totalLength - (1 + $this->cols * 3)) / $this->cols);
            $this->columnWidths = array_fill(0, $this->cols, $length);
            return $this;
        }
        if (count($columnWidths) !== $this->cols) {
            throw new \InvalidArgumentException('Table head and col width should have an equal number of elements');
        }
        foreach ($columnWidths as $index => $size) {
            $this->columnWidths[$index] = $size ?: $this->columnWidths[$index];
        }
        return $this;
    }

    public function rows(array $rows = null): Table|array
    {
        if ($rows === null) {
            return $this->rows;
        }
        foreach ($rows as $row) {
            if ($row === 'hr') {
                continue;
            }
            if (!is_array($row)) {
                throw new \InvalidArgumentException('Row value is not an array.');
            }
            if (count($row) !== $this->cols) {
                throw new \InvalidArgumentException('Table head and rows should have an equal number of elements');
            }
            foreach ($row as $index => $title) {
                $this->columnWidths[$index] = max($this->columnWidths[$index] ?? 0, mb_strlen($title));
            }
        }
        $this->rows = $rows;
        return $this;
    }

    /**
     * @param array|string $row
     * @return $this
     */
    public function row($row): Table
    {
        if (!is_array($row)) {
            $this->rows[] = 'hr';
            return $this;
        }
        $row = array_values($row);
        if (count($row) !== $this->cols) {
            throw new \InvalidArgumentException('Table head and rows should have an equal number of elements');
        }
        foreach ($row as $index => $title) {
            $this->columnWidths[$index] = max($this->columnWidths[$index] ?? 0, mb_strlen($title));
        }
        $this->rows[] = $row;
        return $this;
    }

    public function render(): Table
    {
        $totalLength = $this->output->width() - 2;
        $length = (int)(($totalLength - (1 + $this->cols * 3)) / $this->cols);
        $header = $footer = $hr = $this->renderHr($length);

        if ($this->headerTitle) {
            $titleLength = mb_strlen($this->headerTitle);
            $headerLength = mb_strlen($header);
            $header = sprintf(
                '%s %s %s',
                mb_substr($header, 0, (int)(($headerLength - $titleLength - 2) / 2)),
                $this->headerTitle,
                mb_substr($header, (int)(($headerLength + $titleLength + 2) / 2))
            );
        }
        $this->output->writeln($header);

        $this->output->writeln($this->renderRow($this->header, $length));
        $this->output->writeln($hr);
        foreach ($this->rows as $row) {
            if ($row === 'hr') {
                $this->output->writeln($hr);
                continue;
            }
            $this->output->writeln($this->renderRow($row, $length));
        }

        if ($this->footerTitle) {
            $titleLength = mb_strlen($this->footerTitle);
            $footerLength = mb_strlen($footer);
            $footer = sprintf(
                '%s %s %s',
                mb_substr($header, 0, (int)(($footerLength - $titleLength - 2) / 2)),
                $this->footerTitle,
                mb_substr($header, (int)(($footerLength + $titleLength + 2) / 2))
            );
        }
        $this->output->writeln($footer);
        return $this;
    }

    private function renderHr(int $length): string
    {
        $hr = '+';
        for ($i = 0; $i < $this->cols; $i++) {
            $length = $this->columnWidths[$i] ?: $length;
            $hr .= '-' . str_pad('-', $length, '-', STR_PAD_RIGHT) . '-+';
        }
        return $hr;
    }

    private function renderRow(array $row, int $length): string
    {
        $line = '|';
        foreach ($row as $index => $col) {
            $length = $this->columnWidths[$index] ?: $length;

            $format = $this->format[$index] ?? '%s';
            $col = sprintf($format, $col);
            $col = str_pad($col, $length, ' ', STR_PAD_RIGHT);
            $col = substr($col, 0, $length);
            $line .= ' ' . $col . ' |';
        }
        return $line;
    }

}
