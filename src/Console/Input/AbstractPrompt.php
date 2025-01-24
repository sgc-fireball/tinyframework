<?php

namespace TinyFramework\Console\Input;

use Closure;
use TinyFramework\Console\Output\OutputInterface;
use TinyFramework\Shell\Terminal;

abstract class AbstractPrompt
{

    /** @var Array<string, Closure> */
    private array $events = [];

    protected mixed $result = null;

    public function __construct(
        protected readonly InputInterface $input,
        protected readonly OutputInterface $output,
        protected ?Terminal $terminal = null,
    ) {
        $this->terminal ??= new Terminal();
    }

    public function on(string|array $key, Closure $callback): self
    {
        if (is_array($key)) {
            foreach ($key as $subKey) {
                $this->on($subKey, $callback);
            }
        } else {
            $this->events[$key] = $callback->bindTo($this);
        }
        return $this;
    }

    abstract public function prepare(): void;

    abstract public function render(): void;

    abstract public function cleanup(): void;

    abstract protected function getResult(): mixed;

    public function run(): mixed
    {
        if (!$this->input->interaction()) {
            return $this->getResult();
        }
        $this->terminal->setTtyConfig('-icanon -isig -echo');
        $this->terminal->hideCursor();
        $this->prepare();
        do {
            $this->render();
            $key = fread(STDIN, 10);
            if (array_key_exists($key, $this->events)) {
                if ($this->events[$key]() === false) {
                    break;
                }
            }
        } while (true);
        $this->cleanup();
        $this->terminal->showCursor();
        $this->terminal->restoreTtyConfig();
        return $this->getResult();
    }

}
