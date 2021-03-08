<?php declare(strict_types=1);

namespace TinyFramework\Shell;

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

    public function run()
    {
        #$this->output->error('This is an error.');
        #$this->output->warning('This is an warning.');
        #$this->output->info('This is an info.');
        #$this->output->successful('This is an successful.');
        $this->readline->readHistory();
        while (true) {
            $line = $this->readline->prompt('$');
            if (in_array($line, ['exit', 'quit']) || $line === false) {
                $this->readline->saveHistory();
                break;
            }
            if (empty($line)) {
                continue;
            }
            try {
                $this->execute($line);
            } catch (\Throwable $e) {
                $this->output->error($e->getMessage());
            };
        }
    }

    private function execute(string $__internal__code): void
    {
        $closure = function () use ($__internal__code) {
            try {
                ob_start();
                set_error_handler([$this, 'handleError']);
                $this->context->setVariable('__internal__code', $__internal__code);
                $__internal__variables = $this->context->getVariables();
                extract($__internal__variables);
                unset($__internal__variables);
                eval(rtrim($__internal__code, ';') . ';');
                $this->context->setVariables(get_defined_vars());
                $this->readline->addHistory($__internal__code);
                $this->readline->saveHistory();
                $content = rtrim(ob_get_clean());
                if (!empty($content)) {
                    echo rtrim($content) . PHP_EOL;
                }
            } catch (\Throwable $e) {
                ob_end_clean();
                throw $e;
            } finally {
                restore_error_handler();
            }
        };
        $closure->bindTo($this, get_class($this));
        $closure();
    }

    /**
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @internal
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline)
    {
        throw new \RuntimeException($errstr, $errno);
    }

}
