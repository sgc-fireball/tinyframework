<?php

declare(strict_types=1);

namespace TinyFramework\Hash;

interface HashInterface
{
    public function hash(#[\SensitiveParameter] string $plaintext): string;

    public function verify(#[\SensitiveParameter] string $plaintext, #[\SensitiveParameter] string $hash): bool;
}
