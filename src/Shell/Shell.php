<?php declare(strict_types=1);

namespace TinyFramework\Shell;

use TinyFramework\Console\Output\OutputInterface;

class Shell
{

    private OutputInterface $output;

    private array $variables = [];

    private Readline $readline;

    public function __construct(OutputInterface $output, Readline $readline)
    {
        $this->readline = $readline;
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
            $line = $this->readline->prompt(config('app.name').' $');
            if (empty($line)) {
                continue;
            }
            if ($line === 'exit') {
                $this->readline->saveHistory();
                break;
            }
            try {
                $this->execute($line);
            } catch (\Throwable $e) {
                $this->output->error($e->getMessage());
            };
        }
    }

    private function execute(string $code): void
    {
        $closure = function () use ($code) {
            try {
                ob_start();
                set_error_handler([$this, 'handleError']);
                $this->variables['code'] = $code;
                extract($this->variables);
                eval(rtrim($code, ';') . ';');
                $this->variables = get_defined_vars();
                $this->readline->addHistory($code);
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
