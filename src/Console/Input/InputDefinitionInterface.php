<?php

declare(strict_types=1);

namespace TinyFramework\Console\Input;

interface InputDefinitionInterface
{
    public static function create(
        string $name,
        string $description = null,
        array $options = [],
        array $arguments = []
    ): InputDefinitionInterface;

    public function name(string $name = null): InputDefinitionInterface|string;

    public function description(string $description = null): InputDefinitionInterface|string|null;

    public function argument(Argument|string|int $argument = null): InputDefinitionInterface|Argument|array|null;

    public function option(Option|string $option = null): InputDefinitionInterface|Option|array|null;
}
