<?php declare(strict_types=1);

namespace TinyFramework\Console\Input;

class Argument
{

    const VALUE_REQUIRED = 1;
    const VALUE_OPTIONAL = 2;

    private int $mode = 2;

    private string $name;

    private ?string $description;

    private mixed $value = null;

    public static function create(
        string $name,
        int $mode = null,
        string $description = '',
        mixed $default = null
    ): Argument
    {
        return new Argument($name, $mode, $description, $default);
    }

    public function __construct(
        string $name,
        int $mode = null,
        string $description = '',
        mixed $default = null
    )
    {
        $this->name = $name;
        $this->mode = $mode === null ? self::VALUE_OPTIONAL : $mode;
        $this->description = $description;
        $this->value = $default;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isRequired(): bool
    {
        return ($this->mode & self::VALUE_REQUIRED) === self::VALUE_REQUIRED;
    }

    public function isOptional(): bool
    {
        return ($this->mode & self::VALUE_OPTIONAL) === self::VALUE_OPTIONAL;
    }

    public function value(mixed $value = null): mixed
    {
        if (is_null($value)) {
            return $this->value;
        }
        $this->value = $value;
        return $this;
    }

    public function description(): ?string
    {
        return $this->description;
    }

}
