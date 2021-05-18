<?php

namespace TinyFramework\Validation\Rule;

class Ipv4Rule extends RuleAwesome
{

    public function getName(): string
    {
        return 'ipv4';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }
        return [$this->translator->trans('validation.ipv4', ['attribute' => $this->getTransName($name)])];
    }

}
