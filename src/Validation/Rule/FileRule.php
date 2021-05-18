<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class FileRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'file';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!($file instanceof UploadedFile)) {
            return [$this->translator->trans('validation.file', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
