<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class RequiredRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'required';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $value = $attributes[$name] ?? null;
        if (is_bool($value) || is_numeric($value)) {
            return null;
        }
        if (is_null($value) || (is_string($value) && mb_strlen($value) === 0) || (is_array($value) && count($value) === 0)) {
            return [$this->translator->trans('validation.required', ['attribute' => $this->getTransName($name)])];
        }
        if ($value instanceof UploadedFile && $value->hasError()) {
            return [$this->translator->trans('validation.required', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
