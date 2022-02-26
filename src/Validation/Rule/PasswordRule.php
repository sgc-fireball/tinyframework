<?php

namespace TinyFramework\Validation\Rule;

class PasswordRule extends RuleAwesome
{
    private static array $requirements = [
        'longerThen' => 8,
        'containUpperCase' => true,
        'containLowerCase' => true,
        'containNumerics' => true,
        'containSymbols' => true,
        'notCompromised' => true,
    ];

    public static function mustBeLongerThen(int $chars): string
    {
        self::$requirements['longerThen'] = $chars;
        return static::class;
    }

    public static function mustContainUpperCase(bool $must = true): string
    {
        self::$requirements['containUpperCase'] = $must;
        return static::class;
    }

    public static function mustContainLowerCase(bool $must = true): string
    {
        self::$requirements['containLowerCase'] = $must;
        return static::class;
    }

    public static function mustContainNumerics(bool $must = true): string
    {
        self::$requirements['containNumerics'] = $must;
        return static::class;
    }

    public static function mustContainSymbols(bool $must = true): string
    {
        self::$requirements['containSymbols'] = $must;
        return static::class;
    }

    public static function mustNotCompromised(bool $must = true): string
    {
        self::$requirements['notCompromised'] = $must;
        return static::class;
    }

    public function getName(): string
    {
        return 'password';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $errors = [];
        if (mb_strlen($value) < self::$requirements['longerThen']) {
            $errors[] = $this->translator->trans(
                'validation.password.to_short',
                ['attribute' => $this->getTransName($name)]
            );
        }
        if (self::$requirements['containUpperCase'] && !preg_match('/[A-Z]/', $value)) {
            $errors[] = $this->translator->trans(
                'validation.password.uppercase',
                ['attribute' => $this->getTransName($name)]
            );
        }
        if (self::$requirements['containLowerCase'] && !preg_match('/[a-z]/', $value)) {
            $errors[] = $this->translator->trans(
                'validation.password.lowercase',
                ['attribute' => $this->getTransName($name)]
            );
        }
        if (self::$requirements['containNumerics'] && !preg_match('/[0-9]/', $value)) {
            $errors[] = $this->translator->trans(
                'validation.password.numerics',
                ['attribute' => $this->getTransName($name)]
            );
        }
        if (self::$requirements['containSymbols'] && !preg_match('/[^a-zA-Z0-9]/', $value)) {
            $errors[] = $this->translator->trans(
                'validation.password.symbols',
                ['attribute' => $this->getTransName($name)]
            );
        }

        if (self::$requirements['notCompromised']) {
            $prefix = substr($hash = strtoupper(sha1($value)), 0, 5);
            $postfix = substr($hash, 5);
            if (!extension_loaded('curl')) {
                throw new \RuntimeException('Missing php extension: curl');
            }
            $ch = curl_init(sprintf('https://api.pwnedpasswords.com/range/%s', $prefix));
            curl_setopt_array($ch, [
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_DNS_CACHE_TIMEOUT => 1000,
                CURLOPT_CONNECTTIMEOUT => 1000,
                CURLOPT_TIMEOUT => 1000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Cache-Control: no-cache",
                ],
            ]);
            $response = curl_exec($ch);
            if ($response && curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
                foreach (explode("\n", $response) as $line) {
                    if (!str_starts_with($line, $postfix)) {
                        continue;
                    }
                    if (explode(':', $line, 2)[1] > 0) {
                        $errors[] = $this->translator->trans(
                            'validation.password.pwned',
                            ['attribute' => $this->getTransName($name)]
                        );
                    }
                }
            }
            curl_close($ch);
        }

        return count($errors) ? $errors : null;
    }
}
