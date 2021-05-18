<?php

namespace TinyFramework\Validation\Rule;

class PresentRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'present';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        if (!array_key_exists($name, $attributes)) {
            return [$this->translator->trans('validation.present', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
