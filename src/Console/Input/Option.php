<?php

declare(strict_types=1);

namespace TinyFramework\Console\Input;

use InvalidArgumentException;

class Option
{
    public const VALUE_NONE = 1;
    public const VALUE_REQUIRED = 2;
    public const VALUE_OPTIONAL = 4;
    public const VALUE_IS_ARRAY = 8;

    private string $long;

    private ?string $short;

    private int $mode = 0;

    private ?string $description;

    private mixed $value = null;

    public static function create(
        string $long,
        string $short = null,
        int $mode = null,
        string $description = '',
        mixed $default = null
    ): Option {
        return new Option($long, $short, $mode, $description, $default);
    }

    public function __construct(
        string $long,
        string $short = null,
        int $mode = null,
        string $description = '',
        mixed $default = null
    ) {
        if (mb_strlen($long) === 1) {
            throw new InvalidArgumentException('Long option name is to short: ' . $long);
        }
        if ($short !== null && mb_strlen($short) > 1) {
            throw new InvalidArgumentException('Short option name is to long: ' . $short);
        }

        $this->long = $long;
        $this->short = $short;
        $this->mode = $mode === null ? self::VALUE_NONE | self::VALUE_OPTIONAL : $mode;
        $this->description = $description;
        $this->value = $default;
        if (!$this->hasValue()) {
            $this->value = 0;
        } elseif ($this->isArray()) {
            if (!is_array($this->value)) {
                $this->value = empty($this->value) ? [] : [$this->value];
            }
        }
    }

    public function hasValue(): bool
    {
        return !(($this->mode & self::VALUE_NONE) === self::VALUE_NONE);
    }

    public function isRequired(): bool
    {
        return ($this->mode & self::VALUE_REQUIRED) === self::VALUE_REQUIRED;
    }

    public function isOptional(): bool
    {
        return ($this->mode & self::VALUE_OPTIONAL) === self::VALUE_OPTIONAL;
    }

    public function isArray(): bool
    {
        return ($this->mode & self::VALUE_IS_ARRAY) === self::VALUE_IS_ARRAY;
    }

    public function value(mixed $value = null): mixed
    {
        if ($value === null) {
            return $this->value;
        }
        $this->value = $value;
        return $this;
    }

    public function long(): string
    {
        return $this->long;
    }

    public function short(): ?string
    {
        return $this->short;
    }

    public function description(): ?string
    {
        return $this->description;
    }
}
