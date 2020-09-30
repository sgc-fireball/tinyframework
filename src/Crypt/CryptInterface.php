<?php declare(strict_types=1);

namespace TinyFramework\Crypt;

interface CryptInterface
{

    public function encrypt(string $plaintext, string $key = null): string;

    public function decrypt(string $encrypted, string $key = null): string;

}
