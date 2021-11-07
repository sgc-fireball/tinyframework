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

    public function __construct()
    {
        $this->option(Option::create('help', 'h', null, 'Print the help message.'))
            ->option(Option::create('quiet', 'q', null, 'Do not output any message'))
            ->option(Option::create('verbose', 'v', null, 'Increase the verbose level.'))
            ->option(Option::create('ansi', null, null, 'Force ANSI output'))
            ->option(Option::create('no-ansi', null, null, 'Disable ANSI output'))
            ->option(Option::create('no-interaction', 'n', null, 'Do not ask any interactive question.'));
    }

    public function name(string $name = null): static|string
    {
        if ($name === null) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    public function description(string $description = null): static|string|null
    {
        if ($description === null) {
            return $this->description;
        }
        $this->description = $description;
        return $this;
    }

    public function option(Option|string $option = null): static|Option|array|null
    {
        if ($option === null) {
            return $this->options;
        }
        if (is_string($option)) {
            if (array_key_exists($option, $this->options)) {
                return $this->options[$option];
            }
            return null;
        }
        assert($option instanceof Option);
        $this->options[$option->long()] = $option;
        if ($option->short()) {
            $this->options[$option->short()] = $option;
        }
        return $this;
    }

    public function argument(Argument|string|int $argument = null): static|Argument|array|null
    {
        if ($argument === null) {
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
