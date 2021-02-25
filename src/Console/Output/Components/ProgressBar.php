<?php declare(strict_types=1);

namespace TinyFramework\Console\Output\Components;

use TinyFramework\Console\Output\OutputInterface;

class ProgressBar
{

    private OutputInterface $output;

    private float $startTime = 0;

    private int $max = 100;

    private int $step = 0;

    private string $format = ' {current:%3s}/{max:%3s} [{bar}] {percent:%3s%%}';

    private array $messages = ['message' => ''];

    private static $formats = [
        'normal' => ' {current:%3s}/{max:%3s} [{bar}] {percent:%3s%%}',
        'normal_nomax' => ' {current:%3s} [{bar}]',
        'verbose' => ' {current:%3s}/{max:%3s} [{bar}] {percent:%3s%%} {elapsed}',
        'verbose_nomax' => ' {current:%3s} [{bar}] {elapsed}',
        'very_verbose' => ' {current:%3s}/{max:%3s} [{bar}] {percent:%3s%%} {elapsed}/{estimated}',
        'very_verbose_nomax' => ' {current:%3s} [{bar}] {elapsed}',
        'debug' => ' {current:%3s}/{max:%3s} [{bar}] {percent:%3s%%} {elapsed}/{estimated} {memory:%4s}',
        'debug_nomax' => ' {current:%3s} [{bar}] {elapsed} {memory:%4s}',
    ];

    /**
     * @param string $name
     * @param string $format
     * current: The current step;
     * max: The maximum number of steps (or 0 if no max is defined);
     * bar: The bar itself;
     * percent: The percentage of completion (not available if no max is defined);
     * elapsed: The time elapsed since the start of the progress bar;
     * remaining: The remaining time to complete the task (not available if no max is defined);
     * estimated: The estimated time to complete the task (not available if no max is defined);
     * memory: The current memory usage;
     * message: used to display arbitrary messages in the progress bar (as explained later).
     */
    private static function setFormatDefinition(string $name, string $format)
    {
        self::$formats[$name] = $format;
    }

    public function __construct(OutputInterface $output, int $max = 100)
    {
        $this->output = $output;
        $this->max($max);
        $postfix = $max === 0 ? '_nomax' : '';
        $this->format('normal' . $postfix);
        if ($output->verbosity() === OutputInterface::VERBOSITY_VERBOSE) {
            $this->format('verbose' . $postfix);
        } else if ($output->verbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $this->format('very_verbose' . $postfix);
        } else if ($output->verbosity() >= OutputInterface::VERBOSITY_DEBUG) {
            $this->format('debug' . $postfix);
        }
    }

    public function message(string $text, string $name = 'message')
    {
        if ($text === null) {
            return $this->messages[$name] ?? null;
        }
        $this->messages[$name] = $text;
        return $this;
    }

    /**
     * @param int|null $max
     * @return ProgressBar|int
     */
    public function max(int $max = null)
    {
        if ($max === null) {
            return $this->max;
        }
        $this->max = max(0, $max);
        return $this;
    }

    public function format(string $format = null)
    {
        if ($format === null) {
            return $this->format;
        }
        if (!array_key_exists($format, self::$formats)) {
            throw new \RuntimeException('Unknown format: ' . $format);
        }
        $this->format = self::$formats[$format];
        return $this;
    }

    public function start()
    {
        $this->step = 0;
        $this->startTime = microtime(true);
        return $this;
    }

    public function advance()
    {
        $this->step++;
        // @TODO
        $this->display();
        return $this;
    }

    public function stop()
    {
        $this->step = $this->max;
        $this->display();
        $this->output->write(PHP_EOL);
        return $this;
    }

    public function display()
    {
        if ($this->output->verbosity() === OutputInterface::VERBOSITY_QUIET) {
            return $this;
        }

        $message = "\r" . $this->format;
        $width = max(3, strlen((string)max($this->step, $this->max)));
        $placeholder = array_merge($this->messages, [
            'current' => str_pad((string)$this->step, $width, ' ', STR_PAD_LEFT),
            'max' => $this->max === 0 ? 0 : str_pad((string)$this->max, $width, '', STR_PAD_LEFT),
            'bar' => '',
            'percent' => '',
            'elapsed' => $elapsed = microtime(true) - $this->startTime,
            'remaining' => '',
            'estimated' => '',
            'memory' => size_format(memory_get_usage(true))
        ]);
        if ($this->max > 0) {
            $placeholder['percent'] = str_pad((string)(int)($this->step / $this->max * 100), 3, ' ', STR_PAD_LEFT);
            $placeholder['estimated'] = time_format($placeholder['elapsed'] / $this->step * $this->max);
            $placeholder['remaining'] = time_format($placeholder['elapsed'] / $this->step * ($this->max - $this->step));
        }
        $placeholder['elapsed'] = time_format($placeholder['elapsed']);

        $barSize = $this->output->width() - strlen(vnsprintf($message, $placeholder)) - 1;

        if ($this->max > 0) {
            $completeSize = (int)($barSize * ($placeholder['percent'] / 100));
            $placeholder['bar'] = str_pad($placeholder['bar'], $completeSize, '#', STR_PAD_LEFT);
            $placeholder['bar'] = str_pad($placeholder['bar'], $barSize, '-', STR_PAD_RIGHT);
        } else {
            $position = $elapsed % $barSize;
            $placeholder['bar'] = str_pad($placeholder['bar'], $position - 1, '-', STR_PAD_LEFT);
            $placeholder['bar'] .= '#';
            $placeholder['bar'] = str_pad($placeholder['bar'], $barSize, '-', STR_PAD_RIGHT);
            $placeholder['max'] = '';
        }

        $this->output->write(vnsprintf($message, $placeholder));
        return $this;
    }

}
