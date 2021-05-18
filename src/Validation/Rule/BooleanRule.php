<?php

namespace TinyFramework\Validation\Rule;

class BooleanRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'boolean';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (in_array($value, [true, false, 'true', 'false', 'on', 'off', 'yes', 'no', 0, 1])) {
            return null;
        }
        return [$this->translator->trans('validation.boolean', ['attribute' => $this->getTransName($name)])];
    }

}
