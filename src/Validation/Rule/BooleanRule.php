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
        $value = is_string($value) ? strtolower($value) : $value;
        if (in_array($value, [true, false, 'true', 'false', 'on', 'off', 'y', 'yes', 'n', 'no', '0', '1', 0, 1], true)) {
            return null;
        }
        return [$this->translator->trans('validation.boolean', ['attribute' => $this->getTransName($name)])];
    }

}
