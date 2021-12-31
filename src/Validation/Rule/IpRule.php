<?php

namespace TinyFramework\Validation\Rule;

class IpRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'ip';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return null;
        }
        return [$this->translator->trans('validation.ip', ['attribute' => $this->getTransName($name)])];
    }
}
