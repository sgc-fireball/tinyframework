<?php

namespace TinyFramework\Validation\Rule;

class EmailRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'email';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        if (empty($parameters)) {
            $parameters[] = 'dns';
            $parameters[] = 'rfc';
        }
        $value = $attributes[$name] ?? null;

        if (empty($value)) {
            return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
        }

        if (in_array('dns', $parameters)) {
            list($user, $domain) = explode('@', $value);
            $domain = idn_to_ascii($domain);
            if (!$domain) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
            if (!dns_check_record($domain, 'MX') && !dns_check_record($domain, 'A') && !dns_check_record($domain, 'AAAA')) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }

        if (in_array('rfc', $parameters)) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }
        return null;
    }

}
