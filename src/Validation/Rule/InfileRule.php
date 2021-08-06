<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Http\UploadedFile;

class InfileRule extends RuleAwesome
{

    public function getName(): string
    {
        return 'file';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!is_string($file)) {
            return [$this->translator->trans('validation.infile', ['attribute' => $this->getTransName($name)])];
        }

        if (preg_match(
            '(data:(?P<mime>[^/]+/[^;]+);((?P<charset>charset=[^;]+);){0,1}base64,(?P<content>.*))',
            $file,
            $matches
        )) {
            if (count($parameters)) {
                foreach ($parameters as $mimetype) {
                    if (str_starts_with($matches['mime'], $mimetype)) {
                        return null;
                    }
                }
            } else {
                return null;
            }
        }

        return [$this->translator->trans('validation.infile', ['attribute' => $this->getTransName($name)])];
    }

}
