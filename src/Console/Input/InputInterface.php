<?php

declare(strict_types=1);

namespace TinyFramework\Console\Input;

interface InputInterface
{
    public function __construct(array $argv = null, InputDefinitionInterface $inputDefinition = null);

    public function argv(array $argv = null): array|InputInterface;

    public function inputDefinition(InputDefinitionInterface $inputDefinition = null): InputInterface|InputDefinitionInterface;

    public function parse(): ?string;

    public function option(string $name): ?Option;

    public function argument(string $name): ?Argument;

    public function interaction(bool $interaction = null): bool|InputInterface;
}
