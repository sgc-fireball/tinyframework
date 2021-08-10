<?php

namespace TinyFramework\Validation\Rule;

use DateTimeZone;

class TimezoneRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'timezone';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (in_array($value, DateTimeZone::listIdentifiers())) {
            return null;
        }
        if (is_numeric($value)) {
            $value = null;
        }
        try {
            $value = new DateTimeZone($value);
        } catch (\Throwable $e) {
        }
        if ($value instanceof DateTimeZone) {
            return null;
        }
        return [$this->translator->trans('validation.timezone', ['attribute' => $this->getTransName($name)])];
    }

}
