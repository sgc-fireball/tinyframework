<?php

namespace TinyFramework\Validation\Rule;

class SometimesRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'sometimes';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        if (!array_key_exists($name, $attributes)) {
            return true;
        }
        return null;
    }
}
