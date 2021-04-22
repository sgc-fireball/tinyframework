<?php declare(strict_types=1);

namespace TinyFramework\Shell;

class Context
{

    private array $variables = [];

    public function setVariable(string $key, $value): Context
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): Context
    {
        $this->variables = $variables;
        return $this;
    }

}
