<?php

declare(strict_types=1);

namespace TinyFramework\Auth;

interface Authenticatable
{
    public function getAuthIdentifier(): string;
}
