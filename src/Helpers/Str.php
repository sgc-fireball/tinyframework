<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use TinyFramework\Http\URL;

/**
 * @see https://www.php.net/manual/de/ref.strings.php
 */
class Str implements \Stringable
{
    protected string $value = '';

    public static function factory(string $value = ''): Str
    {
        return new self($value);
    }

    public static function chr(int $codepoint): Str
    {
        return new self(\chr($codepoint));
    }

    public static function httpBuildQuery(
        mixed $data,
        string $numeric_prefix = '',
        string $arg_separator = null,
        int $encoding_type = PHP_QUERY_RFC1738
    ): Str {
        return new self(\http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type));
    }

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function string(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function clone(): self
    {
        return new self($this->value);
    }

    public function slug(string $separator = '-', string $language = null): static
    {
        $locale = setlocale(LC_CTYPE, "0");
        if ($language) {
            setlocale(LC_ALL, $language);
        }
        $value = $this->value;
        if (\in_array(\setlocale(LC_CTYPE, "0"), [null, 'C', 'POSIX'])) {
            if (\str_contains($value = \htmlentities($value, ENT_QUOTES, 'UTF-8'), '&')) {
                $value = \html_entity_decode(
                    \preg_replace(
                        '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i',
                        '$1',
                        $value
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                );
            }
        } else {
            $value = \iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        }
        $flip = $separator === '-' ? '_' : '-';
        $value = \preg_replace('(([a-z])([A-Z]))', '\\1 \\2', $value);
        $value = \preg_replace('![' . \preg_quote($flip) . ']+!u', $separator, $value);
        $value = \str_replace('@', $separator . 'at' . $separator, $value);
        $value = \preg_replace('![^' . \preg_quote($separator) . '\pL\pN\s]+!u', '', \strtolower($value));
        $value = \preg_replace('![' . \preg_quote($separator) . '\s]+!u', $separator, $value);
        $value = \trim($value, $separator);
        $this->value = $value;
        if ($language) {
            setlocale(LC_ALL, $locale);
        }
        return $this;
    }

    public function kebabCase(string $language = null): static
    {
        return $this->slug('-', $language);
    }

    public function snakeCase(string $language = null): static
    {
        return $this->slug('_', (string)$language);
    }

    public function camelCase(string $language = null): static
    {
        $this->value = \str_replace(
            ' ',
            '',
            \lcfirst(\ucwords(\str_replace(['-', '_'], ' ', (string)$this->kebabCase($language))))
        );
        return $this;
    }

    public function lowerCase(): static
    {
        $this->value = \mb_strtolower($this->value);
        return $this;
    }

    public function upperCase(): static
    {
        $this->value = \mb_strtoupper($this->value);
        return $this;
    }

    public function addCSlashes(string $characters): static
    {
        $this->value = \addcslashes($this->value, $characters);
        return $this;
    }

    public function addSlashes(): static
    {
        $this->value = \addslashes($this->value);
        return $this;
    }

    public function chunkSplit(int $length = 76, string $separator = "\r\n"): static
    {
        $this->value = \chunk_split($this->value, $length, $separator);
        return $this;
    }

    public function substr(int $offset, int|null $length = null): Str
    {
        $this->value = \substr($this->value, $offset, $length);
        return $this;
    }

    public function countChars(int $mode = 0): Arr|static
    {
        $result = \count_chars($this->value, $mode);
        if (\is_string($result)) {
            $this->value = $result;
            return $this;
        }
        return Arr::factory($result);
    }

    public function substrReplace(array|string $replace, array|int $offset, array|int|null $length = null): Arr|static
    {
        /** @var string|array $result */
        $result = \substr_replace($this->value, $replace, $offset, $length);
        if (\is_string($result)) {
            $this->value = $result;
            return $this;
        }
        return Arr::factory($result);
    }

    public function wordCount(int $format = 0, string|null $characters = null): Arr|int
    {
        $result = \str_word_count($this->value, $format, $characters);
        return \is_array($result) ? Arr::factory($result) : (int)$result;
    }

    public function htmlEntityDecode(int $flags = ENT_COMPAT, string|null $encoding = null): static
    {
        $this->value = \html_entity_decode($this->value, $flags, $encoding);
        return $this;
    }

    public function htmlEntityEncode(
        int $flags = ENT_COMPAT,
        string|null $encoding = null,
        bool $double_encode = true
    ): static {
        $this->value = \htmlentities($this->value, $flags, $encoding, $double_encode);
        return $this;
    }

    public function htmlSpecialCharsDecode(int $flags = ENT_COMPAT): static
    {
        $this->value = \htmlspecialchars_decode($this->value, $flags);
        return $this;
    }

    public function htmlSpecialCharsEncode(
        int $flags = ENT_COMPAT,
        string|null $encoding = null,
        bool $double_encode = true
    ): static {
        $this->value = \htmlspecialchars($this->value, $flags, $encoding, $double_encode);
        return $this;
    }

    public function lcfirst(): static
    {
        $this->value = \lcfirst($this->value);
        return $this;
    }

    public function ucfirst(): static
    {
        $this->value = \ucfirst($this->value);
        return $this;
    }

    public function prefix(Str|string $prefix): static
    {
        $this->value = $prefix . $this->value;
        return $this;
    }

    public function postfix(Str|string $postfix): static
    {
        $this->value = $this->value . $postfix;
        return $this;
    }

    public function wrap(Str|string $prefix, Str|string $postfix): static
    {
        $this->value = $prefix . $this->value . $postfix;
        return $this;
    }

    public function trim(string $characters = " \n\r\t\v\0"): static
    {
        $this->value = \trim($this->value, $characters);
        return $this;
    }

    public function ltrim(string $characters = " \n\r\t\v\0"): static
    {
        $this->value = \ltrim($this->value, $characters);
        return $this;
    }

    public function rtrim(string $characters = " \n\r\t\v\0"): static
    {
        $this->value = \rtrim($this->value, $characters);
        return $this;
    }

    public function padLeft(int $length, string $pad_string = " "): static
    {
        $this->value = \str_pad($this->value, $length, $pad_string, STR_PAD_LEFT);
        return $this;
    }

    public function padBoth(int $length, string $pad_string = " "): static
    {
        $this->value = \str_pad($this->value, $length, $pad_string, STR_PAD_BOTH);
        return $this;
    }

    public function padRight(int $length, string $pad_string = " "): static
    {
        $this->value = \str_pad($this->value, $length, $pad_string, STR_PAD_RIGHT);
        return $this;
    }

    public function replace(array|string $search, array|string $replace, int &$count = null): static
    {
        $this->value = \str_replace($search, $replace, $this->value, $count);
        return $this;
    }

    public function ireplace(array|string $search, array|string $replace, int &$count = null): static
    {
        $this->value = \str_ireplace($search, $replace, $this->value, $count);
        return $this;
    }

    public function strstr(string $needle, bool $before_needle = false): static
    {
        $this->value = \strstr($this->value, $needle, $before_needle);
        return $this;
    }

    public function stristr(string $needle, bool $before_needle = false): static
    {
        $this->value = \stristr($this->value, $needle, $before_needle);
        return $this;
    }

    public function strtok(string $token): static|bool
    {
        $result = \strtok($this->value, $token);
        if (\is_bool($result)) {
            return false;
        }
        $this->value = $result;
        return $this;
    }

    public function strtr(array|string $from, ?string $to = null): static
    {
        if (\is_array($from)) {
            $this->value = \strtr($this->value, $from);
        } else {
            $this->value = \strtr($this->value, $from, $to);
        }
        return $this;
    }

    public function shuffle(): static
    {
        $this->value = \str_shuffle($this->value);
        return $this;
    }

    public function repeat(int $times): static
    {
        $this->value = \str_repeat($this->value, $times);
        return $this;
    }

    public function reverse(): static
    {
        $this->value = \strrev($this->value);
        return $this;
    }

    public function nl2br(bool $use_xhtml = true): static
    {
        $this->value = \nl2br($this->value, $use_xhtml);
        return $this;
    }

    public function quotedPrintableDecode(): static
    {
        $this->value = \quoted_printable_decode($this->value);
        return $this;
    }

    public function quotedPrintableEncode(): static
    {
        $this->value = \quoted_printable_encode($this->value);
        return $this;
    }

    public function quotemeta(): static
    {
        $this->value = \quotemeta($this->value);
        return $this;
    }

    public function stripTags(array|string|null $allowed_tags = null): static
    {
        $this->value = \strip_tags($this->value, $allowed_tags);
        return $this;
    }

    public function stripCSlashes(): static
    {
        $this->value = \stripcslashes($this->value);
        return $this;
    }

    public function stripSlashes(): static
    {
        $this->value = \stripslashes($this->value);
        return $this;
    }

    public function wordwrap(int $width = 75, string $break = "\n", bool $cut_long_words = false): static
    {
        $this->value = \wordwrap($this->value, $width, $break, $cut_long_words);
        return $this;
    }

    public function strpbrk(string $characters): static|bool
    {
        $result = \strpbrk($this->value, $characters);
        if (\is_bool($result)) {
            return false;
        }
        $this->value = $result;
        return $this;
    }

    public function strrchr(string $needle): static|bool
    {
        $result = \strrchr($this->value, $needle);
        if (\is_bool($result)) {
            return false;
        }
        $this->value = $result;
        return $this;
    }

    public function contains(string $chars): bool
    {
        return \str_contains($this->value, $chars);
    }

    public function startsWith(string $chars): bool
    {
        return \str_starts_with($this->value, $chars);
    }

    public function endsWith(string $chars): bool
    {
        return \str_ends_with($this->value, $chars);
    }

    public function strnatcasecmp(string $string2): int
    {
        return \strnatcasecmp($this->value, $string2);
    }

    public function strnatcmp(string $string2): int
    {
        return \strnatcmp($this->value, $string2);
    }

    public function strncasecmp(string $string2, int $length): int
    {
        return \strncasecmp($this->value, $string2, $length);
    }

    public function strncmp(string $string2, int $length): int
    {
        return \strncmp($this->value, $string2, $length);
    }

    public function position(string $chars): int
    {
        $pos = \mb_strpos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    public function compareCaseInsensitive(string $value): int
    {
        return \strcasecmp($this->value, $value);
    }

    public function compare(string $value): int
    {
        return \strcmp($this->value, $value);
    }

    public function strcoll(string $value): int
    {
        return \strcoll($this->value, $value);
    }

    public function strcspn(string $characters, int $offset = 0, int|null $length = null): int
    {
        return \strcspn($this->value, $characters, $offset, $length);
    }

    public function ord(): int
    {
        return \ord($this->value);
    }

    public function reversePosition(string $chars): int
    {
        $pos = \mb_strrpos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    public function strspn(string $characters, int $offset = 0, int|null $length = null): int
    {
        return \strspn($this->value, $characters, $offset, $length);
    }

    public function caseInsensitivePosition(string $chars): int
    {
        $pos = \mb_stripos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    public function caseInsensitiveReversePosition(string $chars): int
    {
        $pos = \mb_strripos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    public function length(): int
    {
        return \mb_strlen($this->value);
    }

    public function substrCompare(
        string $needle,
        int $offset,
        int|null $length = null,
        bool $case_insensitive = false
    ): int {
        return \substr_compare($this->value, $needle, $offset, $length, $case_insensitive);
    }

    public function substrCount(string $needle, int $offset = 0, int|null $length = null): int
    {
        return \substr_count($this->value, $needle, $offset, $length);
    }

    public function csv2arr(string $separator = ",", string $enclosure = "\"", string $escape = '\\'): Arr
    {
        return Arr::factory(\str_getcsv($this->value, $separator, $enclosure, $escape));
    }

    public function split(int $length = 1): Arr
    {
        return Arr::factory(\mb_str_split($this->value, $length));
    }

    public function explode(string $separator, int $limit = PHP_INT_MAX): Arr
    {
        assert(!empty($separator), 'Parameter #1 $separator of function explode expects non-empty-string.');
        return Arr::factory(\explode($separator, $this->value, $limit));
    }

    public function parseStr(): Arr|null
    {
        \parse_str($this->value, $result);
        return $result ? Arr::factory($result) : null;
    }

    public function parseUrl(int $component = -1): URL|Arr|Str|bool|null|int
    {
        if ($component === -1) {
            return new URL($this->value);
        }
        /** @var array|false|int|null|string $result */
        $result = \parse_url($this->value, $component);
        if (\is_array($result)) {
            return Arr::factory($result);
        } elseif (\is_string($result)) {
            $this->value = $result;
            return $this;
        }
        return $result;
    }

    public function mask(string $character = '*', int $index = null, int $length = null): static
    {
        assert(empty($character), 'Paramter #1 $character must not be empty!');
        $length = $length ?: mb_strlen($this->value);
        if ($index === null) {
            $this->value = str_repeat($character, $length);
            return $this;
        }
        $segment = mb_substr($this->value, $index, $length);
        if ($segment === '') {
            return $this;
        }
        $start = mb_substr($this->value, 0, mb_strpos($this->value, $segment, 0));
        $end = mb_substr($this->value, mb_strpos($this->value, $segment, 0) + mb_strlen($segment));
        $this->value = $start . str_repeat(mb_substr($character, 0, 1), mb_strlen($segment)) . $end;
        return $this;
    }
}
