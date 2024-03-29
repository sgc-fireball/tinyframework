<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Crypt;

use PHPUnit\Framework\TestCase;
use TinyFramework\Crypt\AES256CBC;

class AES256CBCTest extends TestCase
{
    private ?string $key;

    public function setUp(): void
    {
        $this->key = random_bytes(32);
    }

    public function testEncryptAndDecrypt(): void
    {
        $crypt = new AES256CBC($this->key);
        $plaintext = random_bytes(64);
        $this->assertEquals($this->key, $crypt->key());
        $ciphertext = $crypt->encrypt($plaintext);
        $this->assertNotEquals($plaintext, $ciphertext);
        $result = $crypt->decrypt($ciphertext);
        $this->assertEquals($plaintext, $result);
    }
}
