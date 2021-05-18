<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class ImageRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'image';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!($file instanceof UploadedFile)) {
            return [$this->translator->trans('validation.image', ['attribute' => $this->getTransName($name)])];
        }
        if (strpos($file->mimetype(), 'image/') !== 0) {
            return [$this->translator->trans('validation.image', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
