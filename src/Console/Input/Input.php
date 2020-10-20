<?php declare(strict_types=1);

namespace TinyFramework\Console\Input;

class Input implements InputInterface
{

    private array $argv = [];

    private array $tokens = [];

    private InputDefinitionInterface $inputDefinition;

    public function __construct(array $argv = null, InputDefinitionInterface $inputDefinition = null)
    {
        $this->inputDefinition($inputDefinition ?? new InputDefinition());
        $this->argv($argv ?? $_SERVER['argv'] ?? []);
        @array_shift($this->argv); // strip application name
    }

    public function argv(array $argv = null)
    {
        if (!is_null($argv)) {
            $this->argv = $argv;
            return $this;
        }
        return $this->argv;
    }

    public function inputDefinition(InputDefinitionInterface $inputDefinition = null)
    {
        if (!is_null($inputDefinition)) {
            $this->inputDefinition = $inputDefinition;
            return $this;
        }
        return $this->inputDefinition;
    }

    public function option(string $name): ?Option
    {
        $definition = $this->inputDefinition();
        return $definition->option($name);
    }

    public function argument(string $name): ?Argument
    {
        $definition = $this->inputDefinition();
        return $definition->argument($name);
    }

    public function parse(): ?string
    {
        $command = null;
        $this->tokens = $this->argv;
        $argumentPosition = 0;
        while (null !== $token = array_shift($this->tokens)) {
            if (mb_strpos($token, '--') === 0) {
                $this->parseLongOption($token);
            } else if (mb_strpos($token, '-') === 0) {
                $this->parseShortOption($token);
            } else {
                if (is_null($command)) {
                    $command = $token;
                } else {
                    $this->parseArgument($argumentPosition, $token);
                    $argumentPosition++;
                }
            }
        }
        return $command;
    }

    private function parseLongOption(string $token): void
    {
        $token = substr($token, 2);
        $value = null;
        if (strpos($token, '=') > 0) {
            [$token, $value] = explode('=', $token);
        }
        /** @var Option[] $option */
        $option = array_values(array_filter(
            array_values($this->inputDefinition->option()),
            function (Option $option) use ($token) {
                return $option->long() === $token;
            }
        ));
        if (count($option) === 1) {
            $this->parseOption($option[0], $value);
            return;
        }
        throw new \InvalidArgumentException('Invalid option.');
    }

    private function parseShortOption(string $token): void
    {
        $token = substr($token, 1);
        $value = null;
        if (strpos($token, '=') > 0) {
            [$token, $value] = explode('=', $token);
        }
        /** @var Option[] $option */
        $option = array_values(array_filter(
            array_values($this->inputDefinition->option()),
            function (Option $option) use ($token) {
                return $option->short() === $token;
            }
        ));
        if (count($option) === 1) {
            $this->parseOption($option[0], $value);
            return;
        }
        throw new \InvalidArgumentException('Invalid option.');
    }

    private function parseOption(Option $option, string $value = null): void
    {
        if ($option->hasValue()) {
            if ($option->isArray()) {
                $values = $option->value() ?? [];
                $values[] = !is_null($value) ? $value : array_shift($this->tokens);
                $option->value($values);
            } else {
                $option->value(array_shift($this->tokens));
            }
        } else {
            $option->value($option->value() + 1);
        }
    }

    private function parseArgument(int $position, string $token): void
    {
        $argument = $this->inputDefinition->argument($position);
        if (is_null($argument)) {
            throw new \InvalidArgumentException('Too many arguments');
        }
        $argument->value($token);
    }

}
