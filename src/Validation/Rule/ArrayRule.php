<?php

namespace TinyFramework\Validation\Rule;

class ArrayRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'array';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (is_array($value)) {
            return null;
        }
        return [$this->translator->trans('validation.array', ['attribute' => $this->getTransName($name)])];
    }
}
