<?php

declare(strict_types=1);

namespace TinyFramework\Console;

use TinyFramework\Console\Input\Argument;
use TinyFramework\Console\Input\Input;
use TinyFramework\Console\Input\InputDefinitionInterface;
use TinyFramework\Console\Input\InputInterface;
use TinyFramework\Console\Input\Option;
use TinyFramework\Console\Output\Output;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Core\Kernel;
use TinyFramework\Event\EventDispatcherInterface;
use TinyFramework\System\SignalHandler;

class ConsoleKernel extends Kernel implements ConsoleKernelInterface
{
    /** @var CommandAwesome[] */
    private array $commands;

    private ?InputInterface $input;

    private ?OutputInterface $output;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->container->alias(ConsoleKernelInterface::class, Kernel::class);
    }

    protected function boot(): void
    {
        parent::boot();

        $cachePath = storage_dir('cache') . '/commands.php';
        if (env('APP_CACHE', true) && file_exists($cachePath)) {
            $this->commands = require_once($cachePath);
            return;
        }

        $this->loadCommandsByPath(__DIR__ . '/Commands', '\\TinyFramework\\Console\\Commands\\');
        /** @var CommandAwesome $command */
        foreach ($this->container->tagged('commands') as $command) {
            $inputDefinition = $command->configuration();
            $this->commands[$inputDefinition->name()] = $command;
        }
        $this->loadCommandsByPath(root_dir() . '/app/Commands', '\\App\\Commands\\');

        if (env('APP_CACHE', true)) {
            file_put_contents(
                $cachePath,
                '<?php declare(strict_types=1); return unserialize(\'' . serialize($this->commands) . '\');'
            );
        }
    }

    /**
     * @return CommandAwesome[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    private function loadCommandsByPath(string $path, string $namespace = '\\'): void
    {
        if (!is_dir($path)) {
            return;
        }
        $list = scandir($path); // allow real folders and .phar folders
        $list = array_filter($list, fn($f) => str_ends_with($f, '.php'));
        $list = array_map(fn($f) => $path . '/' . $f, $list);
        $classes = array_map(function ($path) use ($namespace) {
            return trim($namespace, '\\') . '\\' . str_replace('.php', '', basename($path));
        }, $list);
        $classes = array_filter($classes, function ($class) {
            return class_exists($class);
        });
        array_map(function ($class) {
            /** @var CommandAwesome $command */
            $command = $this->container->get($class);
            $inputDefinition = $command->configuration();
            if (defined('PHARBIN') && PHARBIN && $inputDefinition->name() === 'tinyframework:package:build') {
                return;
            }
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
        self::$reservedMemory = null; // free 10kb ram
        $verbosity = (int)(isset($this->output) ? $this->output->verbosity() : 0);
        $stacktrace = isset($this->output) && $verbosity >= OutputInterface::VERBOSITY_VERBOSE;
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
        $command = \array_key_exists(0, $argv) ? $argv[0] : null;
        $inputDefinition = $this->input->inputDefinition();
        if (\array_key_exists($command, $this->commands)) {
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

        if (\array_key_exists($command, $this->commands)) {
            return $this->commands[$command]->run($this->input, $this->output);
        }
        if ($command) {
            $method = 'command' . ucfirst($command);
            if (method_exists($this, $method)) {
                return $this->{$method}();
            }
        }

        $this->input->argv([
            'list',
            $this->input->argv()[0] ?? null,
        ]);
        return $this->tryHandle();
    }

    private function commandUsage(InputDefinitionInterface $definition = null): int
    {
        if ($definition === null) {
            return $this->commands['list']->run($this->input, $this->output);
        }

        $this->output->writeln("<white><bold>NAME</bold></white>");
        $this->output->writeln("\t<yellow>" . $definition->name() . "</yellow>\n");
        /** @var Option[] $options */
        $options = $definition->option();
        /** @var Argument[] $arguments */
        $arguments = $definition->argument();
        if ($options || $arguments) {
            $this->output->writeln("<white><bold>SYNOPSIS</bold></white>");
            $message = sprintf(
                "\t<yellow>%s %s %s</yellow>",
                PHP_BINARY,
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
                    if ($option->isArray()) {
                        $message .= '...';
                    }
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
            $this->output->writeln("<white><bold>DESCRIPTION</bold></white>");
            $this->output->writeln("\t" . $description . "\n");
        }

        /** @var Option[] $options */
        if ($options) {
            $this->output->writeln('<white><bold>OPTIONS</bold></white>');
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
            $this->output->writeln('<white><bold>ARGUMENTS</bold></white>');
            foreach ($arguments as $argument) {
                $message = "\t<white>" . $argument->name() . '</white>' . PHP_EOL;
                $message .= "\t    " . $argument->description() . PHP_EOL;
                $this->output->writeln($message);
            }
        }

        if ($sections = $definition->sections()) {
            foreach ($sections as $section => $context) {
                $this->output->writeln('<white><bold>' . $section . '</bold></white>');
                if (is_string($context)) {
                    $this->output->writeln("\t" . $context . "\n");
                } else {
                    foreach ($context as $subSection => $subContext) {
                        $this->output->writeln("\t" . $subSection);
                        $this->output->writeln("\t\t" . $subContext . "\n");
                    }
                    $this->output->writeln("\n");
                }
            }
        }

        return 1;
    }
}
