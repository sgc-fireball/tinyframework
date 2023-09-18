<?php

namespace TinyFramework\Auth;

use Throwable;
use TinyFramework\Exception\HttpException;

class AuthException extends HttpException
{
    protected ?Authenticatable $user = null;

    public function __construct(
        string $message = '',
        int $error = 403,
        Throwable $previous = null,
        Authenticatable $user = null
    ) {
        parent::__construct($message, $error, $previous);
        $this->user = $user;
    }

    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }
}
