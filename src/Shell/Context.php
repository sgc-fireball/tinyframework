<?php declare(strict_types=1);

namespace TinyFramework\Shell;

class Context
{

    private array $variables = [];

    public function setVariable(string $key, $value): static
    {
        $this->variables[$key] = $value;
        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;
        return $this;
    }

}
