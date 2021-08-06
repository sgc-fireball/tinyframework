<?php

namespace TinyFramework\Validation\Rule;

class PasswordRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'password';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $errors = [];
        if (mb_strlen($value) < 8) {
            $errors[] = $this->translator->trans('validation.password.to_short', ['attribute' => $this->getTransName($name)]);
        }
        if (!preg_match('/[A-Z]/', $value)) {
            $errors[] = $this->translator->trans('validation.password.uppercase', ['attribute' => $this->getTransName($name)]);
        }
        if (!preg_match('/[a-z]/', $value)) {
            $errors[] = $this->translator->trans('validation.password.lowercase', ['attribute' => $this->getTransName($name)]);
        }
        if (!preg_match('/[0-9]/', $value)) {
            $errors[] = $this->translator->trans('validation.password.numerics', ['attribute' => $this->getTransName($name)]);
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $value)) {
            $errors[] = $this->translator->trans('validation.password.symbols', ['attribute' => $this->getTransName($name)]);
        }

        $prefix = substr($hash = strtoupper(sha1($value)), 0, 5);
        $postfix = substr($hash, 5);
        // @TODO response = fetch(https://api.pwnedpasswords.com/range/$prefix)
        // @TODO count = search $postfix in response ?? 0
        // @TODO count > 0 then errors = []

        if (extension_loaded('curl')) {
            $ch = curl_init(sprintf('https://api.pwnedpasswords.com/range/%s', $prefix));
            curl_setopt_array($ch, array(
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_CONNECTTIMEOUT => 1000,
                CURLOPT_TIMEOUT => 1000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache"
                ),
            ));
            $response = curl_exec($ch);
            if ($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                foreach (explode("\n", $response) as $line) {
                    if (!str_starts_with($line, $postfix)) {
                        continue;
                    }
                    if (explode(':', $line, 2)[1] > 0) {
                        $errors[] = $this->translator->trans('validation.password.pwned', ['attribute' => $this->getTransName($name)]);
                    }
                }
            }
            curl_close($ch);
        }

        return count($errors) ? $errors : null;
    }

}
