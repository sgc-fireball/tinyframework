<?php

namespace TinyFramework\Auth;

class AuthManager
{
    protected array $config = [
        'enabled' => false,
    ];

    /** @var PermissionInterface[] */
    protected array $permissions = [];

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    public function addPermissions(array $permissions): static
    {
        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }
        return $this;
    }

    public function addPermission(PermissionInterface $permission): static
    {
        $this->permissions[$permission::getPermission()] = $permission;
        return $this;
    }

    public function can(Authenticatable $user = null, string $permission = null, mixed $meta = null): bool
    {
        if (!$this->config['enabled']) {
            return true;
        }
        if (!$user) {
            return false;
        }
        if (!array_key_exists($permission, $this->permissions)) {
            throw new AuthException(
                'Invalid permission: ' . $permission,
                0,
                null,
                $user
            );
        }
        return $this->permissions[$permission]->can($user, $meta);
    }

    public function cannot(Authenticatable $user = null, string $permission = null, mixed $meta = null): bool
    {
        return !$this->can($user, $permission, $meta);
    }

    public function getByAuthIdentifier(string $id): Authenticatable|null
    {
        return null;
    }

    public function getByRememberMeToken(string $token): Authenticatable|null
    {
        return null;
    }
}
