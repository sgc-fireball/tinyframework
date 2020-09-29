<?php declare(strict_types=1);

namespace TinyFramework\Console;

use Composer\Command\BaseCommand;
use TinyFramework\Core\Kernel;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class ConsoleKernel extends Kernel implements ConsoleKernelInterface
{

    /** @var BaseCommand[] */
    protected array $commands = [];

    protected function boot()
    {
        parent::boot();
        foreach ($this->container->tagged('commands') as $command) {
            $this->commands[$command->getName()] = $command;
        }
        $this->loadCommandsByPath((defined('ROOT') ? ROOT : '.') . '/app/Commands', 'App\\Commands\\');
        $this->loadCommandsByPath(__DIR__ . '/Commands', 'TinyFramework\\Console\\Commands\\');
    }

    private function loadCommandsByPath(string $path, string $namespace = '\\'): void
    {
        if (is_dir($path)) {
            foreach (glob($path . '/*.php') as $file) {
                $class = $namespace . substr(basename($file), 0, -4);
                if (class_exists($class)) {
                    $command = new $class;
                    $this->commands[$command->getName()] = $command;
                }
            }
        }
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function handle(ArgvInput $input, ConsoleOutput $output): int
    {
        $command = array_key_exists($input->getFirstArgument(), $this->commands)
            ? $this->commands[$input->getFirstArgument()]
            : null;
        if (is_null($command)) {
            // @TODO implement / execute help and/or list command
            throw new \RuntimeException('Invalid command');
        }
        try {
            return $command->run($input, $output);
        } catch (\Throwable $e) {
            $output->write(sprintf('<error>%s</error>', exception2text($e)));
            return 1;
        }
    }

}
