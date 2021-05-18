<?php

namespace TinyFramework\Validation\Rule;

class StringRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'string';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (is_string($value)) {
            return null;
        }
        return [$this->translator->trans('validation.string', ['attribute' => $this->getTransName($name)])];
    }

}
