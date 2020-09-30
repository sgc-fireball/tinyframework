<?php declare(strict_types=1);

namespace TinyFramework\Hash;

class BCrypt implements HashInterface
{

    private $algorithm = PASSWORD_BCRYPT;

    private int $cost;

    public function __construct(int $cost = 10)
    {
        $this->cost = $cost;
    }

    public function hash(string $plaintext): string
    {
        return password_hash($plaintext, $this->algorithm, ['cost' => $this->cost]);
    }

    public function verify(string $plaintext, string $hash = null): bool
    {
        return password_verify($plaintext, $hash);
    }

}
