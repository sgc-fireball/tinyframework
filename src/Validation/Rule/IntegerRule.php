<?php

namespace TinyFramework\Validation\Rule;

class IntegerRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'integer';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (is_integer($value)) {
            return null;
        }
        return [$this->translator->trans('validation.integer', ['attribute' => $this->getTransName($name)])];
    }
}
