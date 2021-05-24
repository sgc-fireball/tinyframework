<?php

namespace TinyFramework\Validation\Rule;

class JsonRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'json';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        try {
            json_decode($value, false, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [$this->translator->trans('validation.json', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
