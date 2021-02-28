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
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\Http\Response;
use TinyFramework\System\SignalHandler;

class ConsoleKernel extends Kernel implements ConsoleKernelInterface
{

    /** @var CommandAwesome[] */
    private array $commands;

    private ?InputInterface $input;

    private ?OutputInterface $output;

    private string $header = <<<EOF
 _____ _             _____                                            _
|_   _(_)_ __  _   _|  ___| __ __ _ _ __ ___   _____      _____  _ __| | __
  | | | | '_ \| | | | |_ | '__/ _` | '_ ` _ \ / _ \ \ /\ / / _ \| '__| |/ /
  | | | | | | | |_| |  _|| | | (_| | | | | | |  __/\ V  V / (_) | |  |   <
  |_| |_|_| |_|\__, |_|  |_|  \__,_|_| |_| |_|\___| \_/\_/ \___/|_|  |_|\_\
               |___/
EOF;

    protected function boot(): void
    {
        parent::boot();
        $this->loadCommandsByPath(__DIR__ . '/Commands', '\\TinyFramework\\Console\\Commands\\');
        /** @var CommandAwesome $command */
        foreach ($this->container->tagged('commands') as $command) {
            $inputDefinition = $command->configuration();
            $this->commands[$inputDefinition->name()] = $command;
        }
        $this->loadCommandsByPath(root_dir() . '/app/Commands', '\\App\\Commands\\');
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
            $this->container
                ->alias('input', InputInterface::class)
                ->singleton(InputInterface::class, $this->input)
                ->alias('output', OutputInterface::class)
                ->singleton(OutputInterface::class, $this->output);
            SignalHandler::init($this->container->get(EventDispatcherInterface::class));
            return $this->tryHandle();
        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }

    public function handleException(\Throwable $e): int
    {
        $stacktrace = isset($this->output) && $this->output->verbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $message = exception2text($e, $stacktrace);
        if (!isset($this->output)) {
            echo $message . "\n\n";
        } else {
            $this->output->error($message);
        }
        return min(max(1, $e->getCode()), 255);
    }

    private function tryHandle(): int
    {
        $argv = $this->input->argv();
        $command = array_key_exists(0, $argv) ? $argv[0] : null;
        $inputDefinition = $this->input->inputDefinition();
        if (array_key_exists($command, $this->commands)) {
            $inputDefinition = $this->commands[$command]->configuration();
        }
        $this->input->inputDefinition($inputDefinition);

        try {
            $this->input->parse();
        } catch (\InvalidArgumentException $e) {
            $this->output->error($e->getMessage());
            $this->output->write(PHP_EOL);
            return $this->commandUsage($inputDefinition);
        }
        if ($inputDefinition->option('no-ansi')->value()) {
            $this->output->ansi(false);
        }
        if ($inputDefinition->option('ansi')->value()) {
            $this->output->ansi(true);
        }
        if ($verbose = $inputDefinition->option('verbose')->value()) {
            $this->output->verbosity($verbose);
        }
        if ($inputDefinition->option('quiet')->value()) {
            $this->output->quiet(true);
        }
        if ($inputDefinition->option('no-interaction')->value()) {
            $this->input->interaction(false);
        }
        if ($inputDefinition->option('help')->value()) {
            return $this->commandUsage($inputDefinition);
        }

        if (array_key_exists($command, $this->commands)) {
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
        if ($hint !== null) {
            $this->output->writeln(sprintf("<yellow>Command %s not found. Did you mean?</yellow>", $hint));
        } else {
            $this->output->writeln($this->header . PHP_EOL);
            $this->output->writeln("<yellow>Available commands:</yellow>");
        }
        /**
         * @var string $key
         * @var CommandAwesome $command
         */
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

    private function commandUsage(InputDefinitionInterface $definition = null): int
    {
        if ($definition === null) {
            return $this->commandList('usage');
        }
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
                foreach ($options as $key => $option) {
                    if (mb_strlen($key) === 1) {
                        continue;
                    }
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
            foreach ($options as $key => $option) {
                if (mb_strlen($key) === 1) {
                    continue;
                }
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
