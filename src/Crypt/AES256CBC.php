<?php

declare(strict_types=1);

namespace TinyFramework\Crypt;

use TinyFramework\Exception\CryptException;

class AES256CBC implements CryptInterface
{
    private string $cipher = 'aes-256-cbc-hmac-sha256';

    private string $key;

    public function __construct(#[\SensitiveParameter] string $key = null)
    {
        if ($key !== null) {
            $this->key($key);
        }
    }

    public function key(#[\SensitiveParameter] string $key = null): static|string
    {
        if ($key === null) {
            return $this->key;
        }
        self::checkKey($key);

        $this->key = $key;
        return $this;
    }

    public function encrypt(string $plaintext, #[\SensitiveParameter] string $key = null): string
    {
        if ($key === null) {
            $key = $this->key;
        }
        self::checkKey($key);

        $length = openssl_cipher_iv_length($this->cipher);
        $iv = openssl_random_pseudo_bytes($length);
        $encrypted = openssl_encrypt($plaintext, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash('sha256', $iv . $encrypted);
        $iv = bin2hex($iv);
        $encrypted = bin2hex($encrypted);
        return base64_encode($iv . $mac . $encrypted);
    }

    public function decrypt(string $encrypted, #[\SensitiveParameter] string $key = null): string
    {
        if ($key === null) {
            $key = $this->key;
        }
        self::checkKey($key);

        $encrypted = base64_decode($encrypted, true);
        $length = openssl_cipher_iv_length($this->cipher);
        $iv = hex2bin(substr($encrypted, 0, $length * 2));
        $mac = substr($encrypted, $length * 2, 64);
        $encrypted = hex2bin(substr($encrypted, $length * 2 + 64));
        if (!hash_equals(hash('sha256', $iv . $encrypted), $mac)) {
            throw new CryptException('Invalid AES256CBC mac.');
        }
        return openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
    }

    private static function checkKey(#[\SensitiveParameter] string $key): void
    {
        if (mb_strlen($key, '8bit') !== 32) {
            throw new CryptException('Invalid AES256CBC key length.');
        }
    }
}
