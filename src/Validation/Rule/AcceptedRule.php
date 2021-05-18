<?php

namespace TinyFramework\Validation\Rule;

class AcceptedRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'accepted';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $value = is_string($value) ? strtolower($value) : $value;
        if (in_array($value, ['y', 'yes', true, 'true', 'on', 1])) {
            return null;
        }
        return [$this->translator->trans('validation.accepted', ['attribute' => $this->getTransName($name)])];
    }

}
