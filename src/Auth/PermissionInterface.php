<?php

namespace TinyFramework\Auth;

interface PermissionInterface
{
    public static function getPermission(): string;

    public function can(?Authenticatable $authenticatable, mixed $meta = null): bool;
}
