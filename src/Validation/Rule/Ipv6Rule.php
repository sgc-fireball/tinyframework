<?php

namespace TinyFramework\Validation\Rule;

class Ipv6Rule extends RuleAwesome
{

    public function getName(): string
    {
        return 'ipv6';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return null;
        }
        return [$this->translator->trans('validation.ipv6', ['attribute' => $this->getTransName($name)])];
    }

}
