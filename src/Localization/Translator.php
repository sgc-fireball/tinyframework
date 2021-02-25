<?php declare(strict_types=1);

namespace TinyFramework\Localization;

class Translator implements TranslatorInterface
{

    private string $locale = 'en';

    private TranslationLoader $translationLoader;

    public function __construct(TranslationLoader $translationLoader, string $locale)
    {
        $this->translationLoader = $translationLoader;
        $this->locale($locale);
    }

    public function locale(string $locale = null)
    {
        if ($locale === null) {
            return $this->locale;
        }
        $this->locale = $locale;
        return $this;
    }

    public function trans(string $key, array $values = [], string $locale = null): string
    {
        $locale = $locale ?: $this->locale;
        $trans = $this->translationLoader->get($locale, $key);
        return count($values) ? vnsprintf($trans, $values) : $trans;
    }

    public function transChoice(string $key, int $count, array $values = [], string $locale = null): string
    {
        $trans = $this->trans($key, [], $locale);
        $values['count'] = $count;
        $lines = explode('|', $trans);
        foreach ($lines as $line) {
            preg_match('/^\[([\d\s,*]{1,})\]\s{0,}(.*)/s', $line, $matches);
            if (count($matches) !== 3) {
                continue;
            }
            $condition = $matches[1];
            $value = $matches[2];
            if (mb_strpos($condition, ',') !== false) {
                [$from, $to] = explode(',', $condition, 2);
                $from = trim($from);
                $to = trim($to);
                if ($to === '*' && $count >= $from) {
                    return vnsprintf($value, $values);
                } elseif ($from === '*' && $count <= $to) {
                    return vnsprintf($value, $values);
                } elseif ($count >= $from && $count <= $to) {
                    return vnsprintf($value, $values);
                }
            }
            if ($count == $condition) {
                return vnsprintf($value, $values);
            }
        }
        return $key;
    }

}
