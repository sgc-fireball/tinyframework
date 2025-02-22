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

        [$domain, $user] = explode('@', strrev($value), 2);
        $domain = strrev($domain);

        // @TODO https://www.rfc-editor.org/rfc/rfc5321#section-4.1.3
        // user@[1.2.3.4]
        // user@IPv6:2002::1
        // user@domain.org

        if (str_starts_with($domain, '[') && str_ends_with($domain, ']')) {
            $idnDomain = substr($domain, 1, -1);
            if (str_starts_with($idnDomain, 'IPv6:')) {
                $idnDomain = substr($idnDomain, 5);
                if (!filter_var($idnDomain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
                }
            } else if (!filter_var($idnDomain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        } else {
            $idnDomain = idn_to_ascii($domain);
            if (!filter_var($idnDomain, FILTER_VALIDATE_DOMAIN)) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }

        if (in_array('rfc', $parameters)) {
            if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }
        }

        if (in_array('dns', $parameters) || in_array('tcp', $parameters)) {
            $mailserver = filter_var($idnDomain, FILTER_VALIDATE_IP) ? $idnDomain : null;

            if (!$mailserver) {
                $mx = dns_get_record($idnDomain, DNS_MX);
                if (is_array($mx) && array_key_exists(0, $mx)) {
                    $mailserver = $mx[0]['target'];
                }
            }

            if (!$mailserver) {
                $a = dns_get_record($idnDomain, DNS_A);
                if (is_array($a) && array_key_exists(0, $a) && $a[0]['ip']) {
                    $mailserver = $idnDomain;
                }
            }

            if (!$mailserver) {
                $aaaa = dns_get_record($idnDomain, DNS_A);
                if (is_array($aaaa) && array_key_exists(0, $aaaa) && $aaaa[0]['ip']) {
                    $mailserver = $idnDomain;
                }
            }

            if (!$mailserver) {
                return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
            }

            if (in_array('tcp', $parameters)) {
                if (filter_var($mailserver, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $mailserver = '[' . $mailserver . ']';
                }
                try {
                    $fp = fsockopen($mailserver, 25, $errorCode, $errorMessage, 1);
                    if ($fp) {
                        fclose($fp);
                    } else {
                        return [
                            $this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)]),
                        ];
                    }
                } catch (\Throwable $e) {
                    if (str_ends_with($e->getMessage(), ' (Cannot assign requested address)')) {
                        return null;
                    }
                    return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
                }
            }
        }

        return null;
    }

}
