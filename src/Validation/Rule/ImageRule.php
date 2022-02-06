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
        if ($file->hasError()) {
            return [$this->translator->trans('validation.image', ['attribute' => $this->getTransName($name)])];
        }
        if (!str_starts_with($file->mimetype(), 'image/')) {
            return [$this->translator->trans('validation.image', ['attribute' => $this->getTransName($name)])];
        }
        $extensions = ['jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp', 'bmp', 'tiff', 'tif', 'eps', 'svg', 'ico', 'wbmp'];
        if (!in_array(strtolower($file->extension()), $extensions)) {
            return [$this->translator->trans('validation.image', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }
}
