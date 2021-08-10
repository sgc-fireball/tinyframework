<?php

namespace TinyFramework\Validation\Rule;

class FloatRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'float';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (!is_string($value) && is_float($value)) {
            return null;
        }
        return [$this->translator->trans('validation.float', ['attribute' => $this->getTransName($name)])];
    }

}
