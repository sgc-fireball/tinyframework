<?php

namespace TinyFramework\Validation\Rule;

class NumericRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'numeric';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (is_numeric($value)) {
            return null;
        }
        return [$this->translator->trans('validation.numeric', ['attribute' => $this->getTransName($name)])];
    }

}
