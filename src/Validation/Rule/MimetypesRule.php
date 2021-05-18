<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class MimetypesRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'mimetypes';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!($file instanceof UploadedFile)) {
            return [$this->translator->trans('validation.file', ['attribute' => $this->getTransName($name)])];
        }
        if (!in_array($file->mimetype(), $parameters)) {
            return [$this->translator->trans('validation.mimetypes', ['attribute' => $this->getTransName($name), 'values' => implode(', ', $parameters)])];
        }
        return null;
    }

}
