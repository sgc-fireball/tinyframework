<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class VideoRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'video';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!($file instanceof UploadedFile)) {
            return [$this->translator->trans('validation.video', ['attribute' => $this->getTransName($name)])];
        }
        if ($file->hasError()) {
            return [$this->translator->trans('validation.video', ['attribute' => $this->getTransName($name)])];
        }
        if (!str_starts_with($file->mimetype(), 'video/')) {
            return [$this->translator->trans('validation.video', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }
}
