<?php

namespace TinyFramework\Validation\Rule;

class InRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'in';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (!in_array($value, $parameters)) {
            return [$this->translator->trans('validation.float', [
                'attribute' => $this->getTransName($name),
                'values' => implode(', ', $parameters)
            ])];
        }
        return null;
    }

}
