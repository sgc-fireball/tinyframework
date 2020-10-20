<?php declare(strict_types=1);

namespace TinyFramework\Console\Input;

class InputDefinition implements InputDefinitionInterface
{

    private ?string $name = null;

    private ?string $description = null;

    /** @var Argument[] */
    private array $arguments = [];

    /** @var Option[] */
    private array $options = [];

    public static function create(
        string $name,
        string $description = null,
        array $options = [],
        array $arguments = []): InputDefinitionInterface
    {
        $n = (new self())->name($name);
        $n->description($description);
        array_map(function ($option) use ($n) {
            $n->option($option);
        }, array_values($options));
        array_map(function ($argument) use ($n) {
            $n->argument($argument);
        }, array_values($arguments));
        return $n;
    }

    public function name(string $name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    public function description(string $description = null)
    {
        if (is_null($description)) {
            return $this->description;
        }
        $this->description = $description;
        return $this;
    }

    public function option($option = null)
    {
        if (is_null($option)) {
            return $this->options;
        }
        if (is_string($option)) {
            if (array_key_exists($option, $this->options)) {
                return $this->options[$option];
            }
            return null;
        }
        /** @var $option Option */
        $this->options[$option->long()] = $option;
        return $this;
    }

    public function argument($argument = null)
    {
        if (is_null($argument)) {
            return $this->arguments;
        }
        if (is_string($argument)) {
            if (array_key_exists($argument, $this->arguments)) {
                return $this->arguments[$argument];
            }
            return null;
        }
        if (is_int($argument)) {
            $arguments = array_values($this->arguments);
            return array_key_exists($argument, $arguments) ? $arguments[$argument] : null;
        }
        $this->arguments[$argument->name()] = $argument;
        return $this;
    }

}
