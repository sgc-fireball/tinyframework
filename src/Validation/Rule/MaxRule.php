<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class MaxRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'max';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $max = $parameters[0] ?? PHP_FLOAT_MAX;

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

        if ($max < $count) {
            return [$this->translator->trans('validation.max' . $type, ['attribute' => $this->getTransName($name), 'max' => $max])];
        }
        return null;
    }
}
