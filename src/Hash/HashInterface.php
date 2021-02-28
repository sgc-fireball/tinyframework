<?php declare(strict_types=1);

namespace TinyFramework\Hash;

interface HashInterface
{
    
    public function hash(string $plaintext): string;

    public function verify(string $plaintext, string $hash): bool;

}
