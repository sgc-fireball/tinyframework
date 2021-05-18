<?php

namespace TinyFramework\Validation\Rule;

class NotInRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'not_in';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (in_array($value, $parameters)) {
            return [$this->translator->trans('validation.not_in', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
