<?php

declare(strict_types=1);

namespace TinyFramework\Shell;

use RuntimeException;
use TinyFramework\Console\Output\OutputInterface;

class Shell
{
    private OutputInterface $output;

    private Context $context;

    private Readline $readline;

    public function __construct(OutputInterface $output, Readline $readline)
    {
        $this->context = new Context();
        $this->readline = $readline;
        $this->readline->setContext($this->context);
        $this->output = $output;
    }

    public function run(): void
    {
        $this->readline->readHistory();
        while (true) {
            $line = $this->readline->prompt('php$');
            if (\in_array($line, ['exit', 'quit', 'bye', 'cya', 'die'])) {
                break;
            }
            if (empty($line)) {
                continue;
            }
            try {
                $this->execute($line);
                $this->readline->saveHistory();
            } catch (\Throwable $e) {
                $this->output->error(
                    sprintf(
                        "%s[%d]\n%s\nin %s:%d",
                        \get_class($e),
                        $e->getCode(),
                        $e->getMessage(),
                        str_replace(root_dir() . '/', '', $e->getFile()),
                        $e->getLine(),
                    )
                );
                $verbosity = (int)$this->output->verbosity();
                if ($verbosity >= OutputInterface::VERBOSITY_VERBOSE) {
                    $this->output->write("\n");
                    foreach ($e->getTrace() as $index => $trace) {
                        if (\array_key_exists('class', $trace)) {
                            $call = $trace['class'] . $trace['type'] . $trace['function'];
                        } else {
                            $call = $trace['function'];
                        }

                        $color = 'yellow';
                        if (!isset($trace['file'])) {
                            $trace['file'] = 'PHP Internal call';
                            $color = 'orange';
                        }
                        if (str_contains($trace['file'], 'eval()\'d code')) {
                            $trace['file'] = 'eval() code';
                            $color = 'orange';
                        }

                        if ($trace['file'] === __FILE__ && $call === 'eval') {
                            break;
                        }

                        $this->output->write(
                            sprintf(
                                "<green>%2d)</green> <%s>%s:%d</%s>\n    %s\n\n",
                                $index,
                                $color,
                                str_replace(root_dir() . '/', '', $trace['file']),
                                $trace['line'] ?? 0,
                                $color,
                                $call
                            )
                        );
                    }
                }
            }
        }
    }

    private function execute(string $__internal__code): void
    {
        $closure = function () use ($__internal__code) {
            try {
                set_error_handler([$this, 'handleError']);
                $this->context->setVariable('__internal__code', $__internal__code);
                $__internal__variables = $this->context->getVariables();
                extract($__internal__variables);
                unset($__internal__variables);
                eval(rtrim($__internal__code, ';') . ';');
                $this->context->setVariables(get_defined_vars());
                $this->readline->addHistory($__internal__code);
                $this->readline->saveHistory();
            } catch (\Throwable $e) {
                throw $e;
            } finally {
                restore_error_handler();
            }
        };
        $closure->bindTo($this, \get_class($this));
        $closure();
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     * @throws RuntimeException
     * @internal
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        throw new RuntimeException(sprintf('%s in %s:%d', $errstr, $errfile, $errline), $errno);
    }
}
