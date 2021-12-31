<?php

namespace TinyFramework\Validation\Rule;

class InLineFileRule extends RuleAwesome
{
    public function getName(): string
    {
        return 'inlinefile';
    }

    public function validate(array $attributes, string $name, ...$parameters): array|bool|null
    {
        $file = $attributes[$name] ?? null;
        if (!is_string($file)) {
            return [$this->translator->trans('validation.inlinefile', ['attribute' => $this->getTransName($name)])];
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

        return [$this->translator->trans('validation.inlinefile', ['attribute' => $this->getTransName($name)])];
    }
}
