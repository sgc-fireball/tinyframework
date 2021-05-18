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
        if (is_null($value)) {
            return true;
        }
        return null;
    }

}
