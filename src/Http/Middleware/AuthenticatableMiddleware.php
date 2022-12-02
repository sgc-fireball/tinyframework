<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Auth\Authenticatable;
use TinyFramework\Auth\AuthManager;
use TinyFramework\Crypt\AES256CBC;
use TinyFramework\Crypt\CryptInterface;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class AuthenticatableMiddleware implements MiddlewareInterface
{

    const REMEMBERME_IDENTIFIER_KEY = 'rememberme';
    const AUTHENTICATEABLE_IDENTIFIER_KEY = 'authenticatable';

    public function __construct(
        private AuthManager $authManager,
        private CryptInterface $crypt
    )
    {
    }

    public function handle(Request $request, Closure $next, mixed ...$parameters): Response
    {
        $authIdentifier = $this->handleSession($request, $next, ...$parameters)
            ?? $this->handleRememberMe($request, $next, ...$parameters);
        if ($authIdentifier) {
            $request->user($authIdentifier);
            if ($request->session()) {
                $request->session()->user($authIdentifier);
                $request->session()->set(self::AUTHENTICATEABLE_IDENTIFIER_KEY, $authIdentifier->getAuthIdentifier());
            }
        }
        return $next($request);
    }

    public function handleSession(Request $request, Closure $next, mixed ...$parameters): ?Authenticatable
    {
        if (!$request->session()) {
            return null;
        }
        $authIdentifier = $request->session()->get(self::AUTHENTICATEABLE_IDENTIFIER_KEY);
        if (!$authIdentifier) {
            return null;
        }
        return $this->authManager->getByAuthIdentifier($authIdentifier);
    }

    protected function handleRememberMe(Request $request, Closure $next, mixed ...$parameters): ?Authenticatable
    {
        $rememberMeToken = $request->cookie(self::REMEMBERME_IDENTIFIER_KEY);
        if (!$rememberMeToken) {
            return null;
        }
        return $this->authManager->getByRememberMeToken($rememberMeToken);
    }

}
