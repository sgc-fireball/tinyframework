<?php

namespace TinyFramework\Validation\Rule;

use TinyFramework\Localization\TranslatorInterface;

abstract class RuleAwesome implements RuleInterface
{

    public function __construct(protected TranslatorInterface $translator)
    {
    }

    protected function getTransName(string $name): string
    {
        $key = 'validation.attributes.' . $name;
        $trans = $this->translator->trans($key);
        if ($trans === $key) {
            return ucfirst($name);
        }
        return $trans;
    }

}
