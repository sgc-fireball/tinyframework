<?php

namespace TinyFramework\Validation\Rule;

class NullableRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'nullable';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if ($value === null) {
            return true;
        }
        return null;
    }
}
