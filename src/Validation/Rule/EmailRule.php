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
        $idnDomain = idn_to_ascii($domain);
        if (!filter_var($idnDomain, FILTER_VALIDATE_IP) && !filter_var($idnDomain, FILTER_VALIDATE_DOMAIN)) {
            return [$this->translator->trans('validation.email', ['attribute' => $this->getTransName($name)])];
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
