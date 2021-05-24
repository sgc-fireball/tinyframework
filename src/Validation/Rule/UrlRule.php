<?php

namespace TinyFramework\Validation\Rule;

class UrlRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'url';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if ($host = parse_url($value, PHP_URL_HOST)) {
                $host = idn_to_ascii($host);
                if ($host && (dns_check_record($host, 'A') || dns_check_record($host, 'AAAA'))) {
                    return null;
                }
            }
        }
        return [$this->translator->trans('validation.url', ['attribute' => $this->getTransName($name)])];
    }

}
