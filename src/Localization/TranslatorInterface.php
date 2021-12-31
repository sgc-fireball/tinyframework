<?php

declare(strict_types=1);

namespace TinyFramework\Localization;

interface TranslatorInterface
{
    /**
     * @param string|null $locale
     * @return TranslatorInterface|string
     */
    public function locale(string $locale = null): TranslatorInterface|string;

    public function trans(string $key, array $values = [], string $locale = null): string;

    public function transChoice(string $key, int $count, array $values = [], string $locale = null): string;
}
