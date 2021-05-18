<?php

namespace TinyFramework\Validation\Rule;

class TimezoneRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'timezone';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (in_array($value, \DateTimeZone::listIdentifiers())) {
            return null;
        }
        return [$this->translator->trans('validation.timezone', ['attribute' => $this->getTransName($name)])];
    }

}
