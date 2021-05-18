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
        if (strpos($file->mimetype(), 'video/') !== 0) {
            return [$this->translator->trans('validation.video', ['attribute' => $this->getTransName($name)])];
        }
        return null;
    }

}
