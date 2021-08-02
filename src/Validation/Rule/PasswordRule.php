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
        if (mb_strlen($value) < 10) {
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

        return count($errors) ? $errors : null;
    }

}
