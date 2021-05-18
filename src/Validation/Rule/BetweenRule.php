<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class BetweenRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'between';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        $min = $parameters[0] ?? PHP_FLOAT_MIN;
        $max = $parameters[1] ?? PHP_FLOAT_MAX;

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

        if ($count < $min || $max < $count) {
            return [$this->translator->trans('validation.between.' . $type, [
                'attribute' => $this->getTransName($name),
                'min' => $min,
                'max' => $max,
            ])];
        }
        return null;
    }

}
