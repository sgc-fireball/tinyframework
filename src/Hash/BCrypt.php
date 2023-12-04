<?php

declare(strict_types=1);

namespace TinyFramework\Hash;

class BCrypt implements HashInterface
{
    private string $algorithm = PASSWORD_BCRYPT;

    private int $cost;

    public function __construct(int $cost = 10)
    {
        $this->cost = $cost;
    }

    public function hash(#[\SensitiveParameter] string $plaintext): string
    {
        return password_hash($plaintext, $this->algorithm, ['cost' => $this->cost]);
    }

    public function verify(#[\SensitiveParameter] string $plaintext, #[\SensitiveParameter] string $hash): bool
    {
        return password_verify($plaintext, $hash);
    }
}
