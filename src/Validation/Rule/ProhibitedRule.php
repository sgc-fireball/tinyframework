<?php

namespace TinyFramework\Validation\Rule;

class ProhibitedRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'prohibited';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        if (array_key_exists($name, $attributes)) {
            return [$this->translator->trans('validation.prohibited', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
