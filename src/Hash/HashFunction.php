<?php declare(strict_types=1);

namespace TinyFramework\Hash;

class HashFunction implements HashInterface
{

    private string $algorithm;

    public function __construct(string $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    public function hash(string $plaintext): string
    {
        return hash($this->algorithm, $plaintext);
    }

    public function verify(string $plaintext, string $hash): bool
    {
        return hash_equals($hash, $this->hash($plaintext));
    }

}
