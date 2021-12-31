<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class MinRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'min';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $min = $parameters[0] ?? PHP_FLOAT_MIN;

        if ($value instanceof UploadedFile) {
            $type = 'file';
            $count = $value->size();
        } elseif (is_string($value)) {
            $type = 'string';
            $count = mb_strlen($value);
        } elseif (is_array($value)) {
            $type = 'array';
            $count = count($value);
        } else {
            $type = 'numeric';
            $count = floatval($value);
        }

        if ($count < $min) {
            return [$this->translator->trans('validation.min' . $type, ['attribute' => $this->getTransName($name), 'min' => $min])];
        }

        return null;
    }
}
