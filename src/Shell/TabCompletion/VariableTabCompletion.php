<?php

declare(strict_types=1);

namespace TinyFramework\Shell\TabCompletion;

use TinyFramework\Shell\Context;

class VariableTabCompletion implements TabCompletionInterface
{
    private ?Context $context = null;

    public function setContext(?Context $context = null): static
    {
        $this->context = $context;
        return $this;
    }

    public function getMatches(array $info, string $input, int $index): array
    {
        $variables = $this->context ? array_keys($this->context->getVariables()) : [];
        if (empty($input)) {
            return [];
        }
        $variables = array_filter($variables, function (string $name) use ($input) {
            return strpos($name, $input) === 0;
        });
        $variables = array_merge($variables, array_map(function (string $name) {
            return '$' . $name;
        }, $variables));
        return $variables;
    }
}
