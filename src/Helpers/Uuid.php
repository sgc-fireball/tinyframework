<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

/**
 * Rfc4122
 * @link https://www.php.net/manual/de/ref.strings.php
 * @link https://github.com/oittaa/uuid-php/blob/master/src/UUID.php
 * @link https://unicorn-utterances.com/posts/what-are-uuids#UUIDv1
 */
class Uuid
{

    use Macroable;

    /**
     * midnight 15 October 1582 UTC
     */
    private const TIME_OFFSET_INT = 0x01B21DD213814000;
    private const UUID_REGEX = '/^(?:urn:)?(?:uuid:)?(\{)?([0-9a-f]{8})\-?([0-9a-f]{4})\-?([0-9a-f]{4})\-?([0-9a-f]{4})\-?([0-9a-f]{12})(?(1)\}|)$/i';
    public const NAMESPACE_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    public const NAMESPACE_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    public const NAMESPACE_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    public const NAMESPACE_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

    private static ?string $node = null;
    private static int $lastTimestamp = 0;
    private static int $clock = 0;

    /**
     * 12 chars / 48 bits for "node"
     */
    private static function nodeId(): string
    {
        if (self::$node) {
            return self::$node;
        }
        self::$node = node();
        return self::$node;
    }

    /**
     * 16 bits:
     * 8 bits for "clk_seq_hi_res",
     * 8 bits for "clk_seq_low",
     * two most significant bits holds zero and one for variant DCE1.1
     *
     * @param int $timestamp in 100-ns steps
     * @return int 4 Chars / 16 bits for "clockSeq"
     */
    private static function clockSeq(int $timestamp): int
    {
        if (self::$lastTimestamp >= $timestamp) {
            self::$clock += 1;
        } else {
            self::$lastTimestamp = $timestamp;
        }
        return self::$clock;
    }

    public static function v1(int $timeNs = null): string
    {
        $clockTime = intval(($timeNs ?? time_ns()) / 100);
        $time = str_pad(dechex($clockTime + self::TIME_OFFSET_INT), 16, '0', \STR_PAD_LEFT);
        return sprintf(
            '%08s-%04s-1%03s-%04s-%012s',
            // 32 bits for "time_low"
            substr($time, -8),
            // 16 bits for "time_mid"
            substr($time, -12, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 1
            substr($time, -15, 3),
            self::clockSeq($clockTime) & 0x3FFF | 0x8000, // 0x800 = RFC4122 variant
            self::nodeId()
        );
    }

    /**
     * @link https://unicorn-utterances.com/posts/what-happened-to-uuid-v2
     * @param int|null $timeNs
     * @return string
     */
    public static function v2(int $timeNs = null): string
    {
        $clockTime = intval(($timeNs ?? time_ns()) / 100);
        $time = str_pad(dechex($clockTime + self::TIME_OFFSET_INT), 16, '0', \STR_PAD_LEFT);
        return sprintf(
            '%08s-%04s-2%03s-8%01s00-%012s',
            // 32 bits for "time_low"
            substr(str_pad(dechex(posix_getuid()), 8, '0', \STR_PAD_LEFT), 0, 8),
            // 16 bits for "time_mid"
            substr($time, -12, 4),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 1
            substr($time, -15, 3),
            self::clockSeq($clockTime) & 0xF,
            self::nodeId()
        );
    }

    public static function v3(string $namespace, string $name): string
    {
        $nbytes = self::getBytes($namespace);
        $uhex = md5($nbytes . $name);
        return self::uuidFromHex($uhex, 3);
    }

    public static function v3dns(string $name): string
    {
        return self::v3(self::NAMESPACE_DNS, $name);
    }

    public static function v3url(string $name): string
    {
        return self::v3(self::NAMESPACE_URL, $name);
    }

    public static function v3oid(string $name): string
    {
        return self::v3(self::NAMESPACE_OID, $name);
    }

    public static function v3x500(string $name): string
    {
        return self::v3(self::NAMESPACE_X500, $name);
    }

    public static function v4(): string
    {
        return self::uuidFromHex(bin2hex(random_bytes(16)), 4);
    }

    public static function v5(string $namespace, string $name): string
    {
        $nbytes = self::getBytes($namespace);
        $uhex = sha1($nbytes . $name);
        return self::uuidFromHex($uhex, 5);
    }

    public static function v5dns(string $name): string
    {
        return self::v5(self::NAMESPACE_DNS, $name);
    }

    public static function v5url(string $name): string
    {
        return self::v5(self::NAMESPACE_URL, $name);
    }

    public static function v5oid(string $name): string
    {
        return self::v5(self::NAMESPACE_OID, $name);
    }

    public static function v5x500(string $name): string
    {
        return self::v5(self::NAMESPACE_X500, $name);
    }

    public static function v6(int $timeNs = null): string
    {
        $time = intval(($timeNs ?? time_ns()) / 100);
        $timehex = str_pad(dechex($time + self::TIME_OFFSET_INT), 15, '0', \STR_PAD_LEFT);
        $uhex = substr_replace(substr($timehex, -15), '6', -3, 0);
        $uhex .= self::clockSeq($time) & 0x3FFF | 0x8000; // 0x800 = RFC4122 variant
        $uhex .= self::nodeId(); // nod
        return self::uuidFromHex($uhex, 6);
    }

    public static function v7(): string
    {
        $unixtsms = time_ms();
        $uhex = substr(str_pad(dechex($unixtsms), 12, '0', \STR_PAD_LEFT), -12);
        $uhex .= bin2hex(random_bytes(10));
        return self::uuidFromHex($uhex, 7);
    }

    public static function v8(): string
    {
        $timestamp = microtime(false);
        $unixts = intval(substr($timestamp, 11), 10);
        $subsec = intval(substr($timestamp, 2, 7), 10);
        $unixtsms = $unixts * 1000 + intdiv($subsec, 10_000);
        $subsec = intdiv(($subsec % 10_000) << 14, 10_000);
        $subsecA = $subsec >> 2;
        $subsecB = $subsec & 0x03;
        $randB = random_bytes(8);
        $randB[0] = chr(ord($randB[0]) & 0x0f | $subsecB << 4);
        $uhex = substr(str_pad(dechex($unixtsms), 12, '0', \STR_PAD_LEFT), -12);
        $uhex .= '8' . str_pad(dechex($subsecA), 3, '0', \STR_PAD_LEFT);
        $uhex .= bin2hex($randB);
        return self::uuidFromHex($uhex, 8);
    }

    private static function uuidFromHex(string $uhex, int $version): string
    {
        assert(strlen($uhex) === 32);
        return sprintf(
            '%08s-%04s-%04x-%04x-%12s',
            // 32 bits for "time_low"
            substr($uhex, 0, 8),

            // 16 bits for "time_mid"
            substr($uhex, 8, 4),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number
            (hexdec(substr($uhex, 12, 4)) & 0x0fff) | $version << 12,

            // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            // 0x800 = Variant is RFC4122
            (hexdec(substr($uhex, 16, 4)) & 0x3fff) | 0x8000,

            // 48 bits for "node"
            substr($uhex, 20, 12)
        );
    }

    private static function stripExtras(string $uuid): string
    {
        if (preg_match(self::UUID_REGEX, $uuid, $m) !== 1) {
            throw new \InvalidArgumentException('Invalid UUID string: ' . $uuid);
        }
        // Get hexadecimal components of UUID
        return strtolower($m[2] . $m[3] . $m[4] . $m[5] . $m[6]);
    }

    private static function getBytes(string $uuid): string
    {
        return pack('H*', self::stripExtras($uuid));
    }
}
