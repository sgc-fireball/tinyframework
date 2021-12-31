<?php

namespace TinyFramework\Validation\Rule;

class ConfirmedRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'confirmed';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value1 = $attributes[$name] ?? null;
        $value2 = $attributes[$name . '_confirmed'] ?? null;
        if ($value1 === $value2) {
            return null;
        }
        return [$this->translator->trans('validation.confirmed', ['attribute' => $this->getTransName($name)])];
    }
}
