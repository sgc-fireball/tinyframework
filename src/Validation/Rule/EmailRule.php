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
            $parameters[] = 'rfc';
        }
        $value = $attributes[$name] ?? null;

        if (!preg_match('/^.{1,}@.{1,}$/', $value)) {
            return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
        }

        list($domain, $user) = explode('@', strrev($value), 2);
        $domain = strrev($domain);
        $domain = idn_to_ascii($domain);
        if (!filter_var($domain, FILTER_VALIDATE_IP) && !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
        }
        if (filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            $domain = $domain . '.';
        }

        if (in_array('dns', $parameters)) {
            if (!$domain) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
            if (!dns_check_record($domain, 'MX') && !dns_check_record($domain, 'A') && !dns_check_record($domain, 'AAAA')) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }

        // @TODO implement a smtp tcp port 25 check

        if (in_array('rfc', $parameters)) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }
        return null;
    }
}
