<?php

declare(strict_types=1);

namespace TinyFramework\Console\Input;

use InvalidArgumentException;

class Input implements InputInterface
{
    private array $argv = [];

    private array $tokens = [];

    private bool $interaction = true;

    private InputDefinitionInterface $inputDefinition;

    public function __construct(array $argv = null, InputDefinitionInterface $inputDefinition = null)
    {
        if ($inputDefinition === null) {
            $inputDefinition = InputDefinition::create('', 'The TinyFramework console command.');
        }
        $this->inputDefinition($inputDefinition);
        $this->argv($argv ?? $_SERVER['argv'] ?? []);
        @array_shift($this->argv); // strip application name
    }

    public function argv(array $argv = null): array|static
    {
        if ($argv !== null) {
            $this->argv = $argv;
            return $this;
        }
        return $this->argv;
    }

    public function inputDefinition(InputDefinitionInterface $inputDefinition = null): static|InputDefinitionInterface
    {
        if ($inputDefinition !== null) {
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

    public function interaction(bool $interaction = null): bool|static
    {
        if ($interaction === null) {
            return $this->interaction;
        }
        $this->interaction = $interaction;
        return $this;
    }

    public function parse(): ?string
    {
        $command = null;
        $this->tokens = $this->argv;
        $argumentPosition = 0;
        while (null !== $token = array_shift($this->tokens)) {
            if (mb_strpos($token, '--') === 0) {
                $this->parseLongOption($token);
            } elseif (mb_strpos($token, '-') === 0) {
                $this->parseShortOption($token);
            } else {
                if ($command === null) {
                    $command = $token;
                } else {
                    $this->parseArgument($argumentPosition, $token);
                    $argumentPosition++;
                }
            }
        }
        
        $interaction = $_SERVER['DEBIAN_FRONTEND'] ?? $_ENV['DEBIAN_FRONTEND'] ?? getenv('DEBIAN_FRONTEND');
        if ($interaction === 'noninteractive') {
            $this->interaction = false;
        }
        return $command;
    }

    private function parseLongOption(string $token): void
    {
        $token = substr($token, 2); // remove the double dash
        $value = null;
        if (strpos($token, '=') > 0) {
            [$token, $value] = explode('=', $token);
        }
        foreach (array_values($this->inputDefinition->option()) as $option) {
            if ($option->long() === $token) {
                $this->parseOption($option, $value);
                return;
            }
        }
        throw new InvalidArgumentException('Invalid long option: ' . $token);
    }

    private function parseShortOption(string $token): void
    {
        $token = mb_substr($token, 1); // remove the dash
        while (mb_strlen($token)) {
            if ($option = $this->inputDefinition->option($token[0])) {
                if ($option->hasValue()) {
                    $value = null;
                    if (mb_strlen($token) > 1) {
                        $value = mb_substr($token, 1, 1) === '=' ? mb_substr($token, 2) : mb_substr($token, 1);
                    }
                    $this->parseOption($option, $value);
                    break;
                } else {
                    $this->parseOption($option, null);
                    $token = mb_substr($token, 1); // remove current option char
                }
                continue;
            }
            throw new InvalidArgumentException('Invalid short option: ' . $token);
        }
    }

    private function parseOption(Option $option, string $value = null): void
    {
        if ($option->hasValue()) {
            if ($option->isArray()) {
                $values = $option->value() ?? [];
                $values[] = $value !== null ? $value : array_shift($this->tokens);
                $option->value($values);
            } else {
                $option->value($value !== null ? $value : array_shift($this->tokens));
            }
        } else {
            $option->value($option->value() + 1);
        }
    }

    private function parseArgument(int $position, string $token): void
    {
        $argument = $this->inputDefinition->argument($position);
        if ($argument === null) {
            throw new InvalidArgumentException('Too many arguments');
        }
        $argument->value($token);
    }
}
