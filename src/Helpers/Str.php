<?php declare(strict_types=1);

namespace TinyFramework\Helpers;

use JetBrains\PhpStorm\Pure;
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

    public static function httpBuildQuery(mixed $data, string $numeric_prefix = null, string $arg_separator = null, int $encoding_type = PHP_QUERY_RFC1738): Str
    {
        return new self(\http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type));
    }

    public function __construct(string $value = '')
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function slug(string $separator = '-', string $language = null): Str
    {
        $value = $this->value;
        if (\in_array(\setlocale(LC_CTYPE, "0"), [null, 'C', 'POSIX'])) {
            if (\str_contains($value = \htmlentities($value, ENT_QUOTES, 'UTF-8'), '&')) {
                $value = \html_entity_decode(\preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $value), ENT_QUOTES, 'UTF-8');
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
        return new self($value);
    }

    public function kebabCase(string $language = null): Str
    {
        return $this->slug('-', $language);
    }

    public function snakeCase(string $language = null): Str
    {
        return $this->slug('_', (string)$language);
    }

    public function camelCase(string $language = null): Str
    {
        return new self(\str_replace(' ', '', \lcfirst(\ucwords(\str_replace(['-', '_'], ' ', (string)$this->kebabCase($language))))));
    }

    public function lowerCase(): Str
    {
        return new self(\mb_strtolower($this->value));
    }

    public function upperCase(): Str
    {
        return new self(\mb_strtoupper($this->value));
    }

    public function addCSlashes(string $characters): Str
    {
        return new self(\addcslashes($this->value, $characters));
    }

    public function addSlashes(): Str
    {
        return new self(\addslashes($this->value));
    }

    public function chunkSplit(int $length = 76, string $separator = "\r\n"): Str
    {
        return new self(\chunk_split($this->value, $length, $separator));
    }

    public function substr(int $offset, int|null $length = null): Str
    {
        return new self(\substr($this->value, $offset, $length));
    }

    public function countChars(int $mode = 0): Arr|Str
    {
        $result = \count_chars($this->value, $mode);
        return \is_string($result) ? new self($result) : Arr::factory($result);
    }

    public function substrReplace(array|string $replace, array|int $offset, array|int|null $length = null): Arr|Str
    {
        $result = \substr_replace($this->value, $replace, $offset, $length);
        return \is_string($result) ? new self($result) : Arr::factory($result);
    }

    public function wordCount(int $format = 0, string|null $characters = null): Arr|int
    {
        $result = \str_word_count($this->value, $format, $characters);
        return \is_array($result) ? Arr::factory($result) : (int)$result;
    }

    public function htmlEntityDecode(int $flags = ENT_COMPAT, string|null $encoding = null): Str
    {
        return new self(\html_entity_decode($this->value, $flags, $encoding));
    }

    public function htmlEntityEncode(int $flags = ENT_COMPAT, string|null $encoding = null, bool $double_encode = true): Str
    {
        return new self(\htmlentities($this->value, $flags, $encoding, $double_encode));
    }

    public function htmlSpecialCharsDecode(int $flags = ENT_COMPAT): Str
    {
        return new self(\htmlspecialchars_decode($this->value, $flags));
    }

    public function htmlSpecialCharsEncode(int $flags = ENT_COMPAT, string|null $encoding = null, bool $double_encode = true): Str
    {
        return new self(\htmlspecialchars($this->value, $flags, $encoding, $double_encode));
    }

    public function lcfirst(): Str
    {
        return new self(\lcfirst($this->value));
    }

    public function ucfirst(): Str
    {
        return new self(\ucfirst($this->value));
    }

    public function prefix(Str|string $prefix): Str
    {
        return new self($prefix . $this->value);
    }

    public function postfix(Str|string $postfix): Str
    {
        return new self($this->value . $postfix);
    }

    public function trim(string $characters = " \n\r\t\v\0"): Str
    {
        return new self(\trim($this->value, $characters));
    }

    public function ltrim(string $characters = " \n\r\t\v\0"): Str
    {
        return new self(\ltrim($this->value, $characters));
    }

    public function rtrim(string $characters = " \n\r\t\v\0"): Str
    {
        return new self(\rtrim($this->value, $characters));
    }

    public function padLeft(int $length, string $pad_string = " "): Str
    {
        return new self(\str_pad($this->value, $length, $pad_string, STR_PAD_LEFT));
    }

    public function padBoth(int $length, string $pad_string = " "): Str
    {
        return new self(\str_pad($this->value, $length, $pad_string, STR_PAD_BOTH));
    }

    public function padRight(int $length, string $pad_string = " "): Str
    {
        return new self(\str_pad($this->value, $length, $pad_string, STR_PAD_RIGHT));
    }

    public function replace(array|string $search, array|string $replace, int &$count = null): Str
    {
        return new self(\str_replace($search, $replace, $this->value, $count));
    }

    public function ireplace(array|string $search, array|string $replace, int &$count = null): Str
    {
        return new self(\str_ireplace($search, $replace, $this->value, $count));
    }

    public function strstr(string $needle, bool $before_needle = false): Str
    {
        return new self(\strstr($this->value, $needle, $before_needle));
    }

    public function stristr(string $needle, bool $before_needle = false): Str
    {
        return new self(\stristr($this->value, $needle, $before_needle));
    }

    public function strtok(string $token): Str|bool
    {
        $result = \strtok($this->value, $token);
        return \is_bool($result) ? false : new self($result);
    }

    public function strtr(array|string $from, ?string $to = null): Str
    {
        if (\is_array($from)) {
            return new self(\strtr($this->value, $from));
        }
        return new self(\strtr($this->value, $from, $to));
    }

    public function shuffle(): Str
    {
        return new self(\str_shuffle($this->value));
    }

    public function repeat(int $times): Str
    {
        return new self(\str_repeat($this->value, $times));
    }

    public function reverse(): Str
    {
        return new self(\strrev($this->value));
    }

    public function nl2br(bool $use_xhtml = true): Str
    {
        return new self(\nl2br($this->value, $use_xhtml));
    }

    public function quotedPrintableDecode(): Str
    {
        return new self(\quoted_printable_decode($this->value));
    }

    public function quotedPrintableEncode(): Str
    {
        return new self(\quoted_printable_encode($this->value));
    }

    public function quotemeta(): Str
    {
        return new self(\quotemeta($this->value));
    }

    public function stripTags(array|string|null $allowed_tags = null): Str
    {
        return new self(\strip_tags($this->value, $allowed_tags));
    }

    public function stripCSlashes(): Str
    {
        return new self(\stripcslashes($this->value));
    }

    public function stripSlashes(): Str
    {
        return new self(\stripslashes($this->value));
    }

    public function wordwrap(int $width = 75, string $break = "\n", bool $cut_long_words = false): Str
    {
        return new self(\wordwrap($this->value, $width, $break, $cut_long_words));
    }

    public function strpbrk(string $characters): Str|bool
    {
        $result = \strpbrk($this->value, $characters);
        return \is_bool($result) ? false : new self($result);
    }

    public function strrchr(string $needle): Str|bool
    {
        $result = \strrchr($this->value, $needle);
        return \is_bool($result) ? false : new self($result);
    }

    #[Pure] public function contains(string $chars): bool
    {
        return \str_contains($this->value, $chars);
    }

    #[Pure] public function startsWith(string $chars): bool
    {
        return \str_starts_with($this->value, $chars);
    }

    #[Pure] public function endsWith(string $chars): bool
    {
        return \str_ends_with($this->value, $chars);
    }

    #[Pure] public function strnatcasecmp(string $string2): int
    {
        return \strnatcasecmp($this->value, $string2);
    }

    #[Pure] public function strnatcmp(string $string2): int
    {
        return \strnatcmp($this->value, $string2);
    }

    #[Pure] public function strncasecmp(string $string2, int $length): int
    {
        return \strncasecmp($this->value, $string2, $length);
    }

    #[Pure] public function strncmp(string $string2, int $length): int
    {
        return \strncmp($this->value, $string2, $length);
    }

    #[Pure] public function position(string $chars): int
    {
        $pos = \mb_strpos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    #[Pure] public function compareCaseInsensitive(string $value): int
    {
        return \strcasecmp($this->value, $value);
    }

    #[Pure] public function compare(string $value): int
    {
        return \strcmp($this->value, $value);
    }

    #[Pure] public function strcoll(string $value): int
    {
        return \strcoll($this->value, $value);
    }

    #[Pure] public function strcspn(string $characters, int $offset = 0, int|null $length = null): int
    {
        return \strcspn($this->value, $characters, $offset, $length);
    }

    #[Pure] public function ord(): int
    {
        return \ord($this->value);
    }

    #[Pure] public function reversePosition(string $chars): int
    {
        $pos = \mb_strrpos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    #[Pure] public function strspn(string $characters, int $offset = 0, int|null $length = null): int
    {
        return \strspn($this->value, $characters, $offset, $length);
    }

    #[Pure] public function caseInsensitivePosition(string $chars): int
    {
        $pos = \mb_stripos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    #[Pure] public function caseInsensitiveReversePosition(string $chars): int
    {
        $pos = \mb_strripos($this->value, $chars);
        return $pos === false ? -1 : $pos;
    }

    #[Pure] public function length(): int
    {
        return \mb_strlen($this->value);
    }

    #[Pure] public function substrCompare(string $needle, int $offset, int|null $length = null, bool $case_insensitive = false): int
    {
        return \substr_compare($this->value, $needle, $offset, $length, $case_insensitive);
    }

    #[Pure] public function substrCount(string $needle, int $offset = 0, int|null $length = null): int
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
        $result = \parse_url($this->value, $component);
        if (\is_array($result)) return Arr::factory($result);
        if (\is_string($result)) return new self($result);
        return $result;
    }

}
