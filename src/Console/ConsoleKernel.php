<?php declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\Input;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\Output;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\Kernel;

class ConsoleKernel extends Kernel implements ConsoleKernelInterface
{

    /** @var CommandAwesome[] */
    private array $commands = [];

    private ?InputInterface $input;

    private ?OutputInterface $output;

    protected function boot()
    {
        parent::boot();
        $this->loadCommandsByPath(__DIR__ . '/Commands', '\\TinyFramework\\Console\\Commands\\');
        foreach ($this->container->tagged('commands') as $command) {
            /** @var $command CommandAwesome */
            $inputDefinition = $command->configuration();
            $this->commands[$inputDefinition->name()] = $command;
        }
        $this->loadCommandsByPath((defined('ROOT') ? ROOT : '.') . '/app/Commands', '\\App\\Commands\\');
    }

    private function loadCommandsByPath(string $path, string $namespace = '\\'): void
    {
        if (!is_dir($path)) {
            return;
        }
        $classes = array_map(function ($path) use ($namespace) {
            return trim($namespace, '\\') . '\\' . str_replace('.php', '', basename($path));
        }, glob($path . '/*.php'));
        $classes = array_filter($classes, function ($class) {
            return class_exists($class);
        });
        array_map(function ($class) {
            /** @var CommandAwesome $command */
            $command = $this->container->get($class);
            $inputDefinition = $command->configuration();
            $this->commands[$inputDefinition->name()] = $command;
        }, $classes);
    }

    public function handle(InputInterface $input = null, OutputInterface $output = null): int
    {
        try {
            $this->input = $input ?? new Input();
            $this->output = $output ?? new Output();
            $exitCode = $this->tryHandle();
        } catch (\Throwable $e) {
            $this->output->write("\n\n" . exception2text($e) . "\n\n");
            $exitCode = min(max(1, $e->getCode()), 255);
        }
        return min(max(0, $exitCode), 255);
    }

    private function tryHandle(): int
    {
        $argv = $this->input->argv();
        $command = array_key_exists(0, $argv) ? $argv[0] : null;
        if (array_key_exists($command, $this->commands)) {
            $inputDefinition = $this->commands[$command]->configuration();
            $this->input->inputDefinition($inputDefinition);
            $this->input->parse();
            if ($inputDefinition->option('no-ansi')->value()) {
                $this->output->ansi(false);
            }
            if ($inputDefinition->option('ansi')->value()) {
                $this->output->ansi(true);
            }
            if ($inputDefinition->option('help')->value()) {
                return $this->commandUsage($inputDefinition);
            }
            return $this->commands[$command]->run($this->input, $this->output);
        }
        if ($command) {
            $method = 'command' . ucfirst($command);
            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }
        return $this->commandList($command);
    }

    private function commandList(string $hint = null): int
    {
        /**
         * @var string $key
         * @var CommandAwesome $command
         */
        if ($hint !== null) {
            $this->output->writeln("<yellow>Did you mean?</yellow>");
        } else {
            $this->output->writeln("<yellow>Available commands:</yellow>");
        }
        foreach ($this->commands as $key => $command) {
            $configuration = $command->configuration();
            if ($hint === null || mb_strpos($configuration->name(), $hint) !== false) {
                $this->output->writeln(sprintf(
                    "  <green>%s</green>%s",
                    str_pad($configuration->name(), 32),
                    $configuration->description()
                ));
            }
        }
        return 0;
    }

    private function commandUsage(InputDefinitionInterface $definition): int
    {
        $this->output->writeln("<white>NAME</white>");
        $this->output->writeln("\t<yellow>" . $definition->name() . "</yellow>\n");
        /** @var Option[] $options */
        $options = $definition->option();
        /** @var Argument[] $arguments */
        $arguments = $definition->argument();
        if ($options || $arguments) {
            $this->output->writeln("<white>SYNOPSIS</white>");
            $message = sprintf(
                "\t<yellow>%s %s %s</yellow>",
                $_SERVER['_'],
                $_SERVER['SCRIPT_NAME'],
                $definition->name()
            );
            if ($options) {
                foreach ($options as $long => $option) {
                    $message .= ' ';
                    $message .= $option->isRequired() ? '<' : '[';
                    if ($option->short()) {
                        $message .= '-' . $option->short() . '|';
                    }
                    $message .= '--' . $option->long();
                    if ($option->hasValue()) {
                        $message .= ' <value>';
                    }
                    $message .= $option->isRequired() ? '>' : ']';
                }
            }
            if ($arguments) {
                foreach ($arguments as $argument) {
                    $message .= ' ';
                    $message .= $argument->isRequired() ? '<' : '[';
                    $message .= $argument->name();
                    $message .= $argument->isRequired() ? '>' : ']';
                }
            }

            $this->output->writeln($message . "\n");
        }
        if ($description = $definition->description()) {
            $this->output->writeln("<white>DESCRIPTION</white>");
            $this->output->writeln("\t" . $description . "\n");
        }

        /** @var Option[] $options */
        if ($options) {
            $this->output->writeln('<white>OPTIONS</white>');
            foreach ($options as $long => $option) {
                $message = "\t<white>";
                if ($option->short()) {
                    $message .= '-' . $option->short() . ', ';
                }
                $message .= '--' . $option->long();
                if ($option->hasValue()) {
                    $message .= ' <value>';
                }
                $message .= '</white>' . PHP_EOL . "\t    ";
                $message .= $option->description() . PHP_EOL;
                $this->output->writeln($message);
            }
        }

        /** @var Argument[] $arguments */
        if ($arguments) {
            $this->output->writeln('<white>ARGUMENTS</white>');
            foreach ($arguments as $argument) {
                $message = "\t<white>" . $argument->name() . '</white>' . PHP_EOL;
                $message .= "\t    " . $argument->description() . PHP_EOL;
                $this->output->writeln($message);
            }
        }
        return 1;
    }

}
