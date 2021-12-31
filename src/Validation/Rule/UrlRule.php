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
                if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return null;
                }
                if (str_starts_with($host, '[') && str_ends_with($host, ']') && filter_var(substr($host, 1, -1), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return null;
                }
                $host = idn_to_ascii($host);
                if ($host) {
                    $host .= '.';
                    if ((dns_check_record($host, 'A') || dns_check_record($host, 'AAAA'))) {
                        return null;
                    }
                }
            }
        }
        return [$this->translator->trans('validation.url', ['attribute' => $this->getTransName($name)])];
    }
}
