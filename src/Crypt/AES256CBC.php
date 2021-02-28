<?php declare(strict_types=1);

namespace TinyFramework\Crypt;

use TinyFramework\Exception\CryptException;

class AES256CBC implements CryptInterface
{

    private string $cipher = 'aes-256-cbc-hmac-sha256';

    private string $key;

    public function __construct(string $key = null)
    {
        if (!is_null($key)) {
            $this->key($key);
        }
    }

    public function key(string $key = null): AES256CBC|string
    {
        if (is_null($key)) {
            return $this->key;
        }
        if (mb_strlen($key, '8bit') !== 32) {
            throw new CryptException('Invalid AES256CBC key length.');
        }
        $this->key = $key;
        return $this;
    }

    public function encrypt(string $plaintext, string $key = null): string
    {
        if (is_null($key)) {
            $key = $this->key;
        }
        if (mb_strlen($key, '8bit') !== 32) {
            throw new CryptException('Invalid AES256CBC key length.');
        }
        $iv = openssl_random_pseudo_bytes(32);
        $encrypted = openssl_encrypt($plaintext, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
        $mac = hash('sha256', $iv . $encrypted);
        $iv = bin2hex($iv);
        $encrypted = bin2hex($encrypted);
        return base64_encode($iv . $mac . $encrypted);
    }

    public function decrypt(string $encrypted, string $key = null): string
    {
        if (is_null($key)) {
            $key = $this->key;
        }
        if (mb_strlen($key, '8bit') !== 32) {
            throw new CryptException('Invalid AES256CBC key length.');
        }

        $encrypted = base64_decode($encrypted);
        $iv = hex2bin(substr($encrypted, 0, 64));
        $mac = substr($encrypted, 64, 64);
        $encrypted = hex2bin(substr($encrypted, 128));
        if (!hash_equals(hash('sha256', $iv . $encrypted), $mac)) {
            throw new CryptException('Invalid AES256CBC mac.');
        }
        return openssl_decrypt($encrypted, $this->cipher, $key, OPENSSL_RAW_DATA, $iv);
    }

}
