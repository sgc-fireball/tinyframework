<?php

namespace TinyFramework\Validation\Rule;

class FilledRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'filled';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        if (!array_key_exists($name, $attributes)) {
            return true;
        }
        $value = $attributes[$name];
        if (is_bool($value) || is_numeric($value)) {
            return null;
        }
        if ($value === null || (is_string($value) && mb_strlen($value) === 0) || (is_array($value) && count($value) === 0)) {
            return [$this->translator->trans('validation.filled', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }
}
