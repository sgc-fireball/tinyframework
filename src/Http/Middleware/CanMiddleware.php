<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Auth\Authenticatable;
use TinyFramework\Auth\AuthException;
use TinyFramework\Auth\AuthManager;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class CanMiddleware implements MiddlewareInterface
{

    public function __construct(
        protected AuthManager $gateManager
    ) {
    }

    public function handle(Request $request, Closure $next, mixed ...$parameters): Response
    {
        $user = $request->user();
        if (!($user instanceof Authenticatable)) {
            throw new AuthException('Please login.', 401);
        }
        foreach ($parameters as $permission) {
            if (!$this->gateManager->can($user, $permission)) {
                throw new AuthException('Access denied to ' . $permission, 403, null, $user);
            }
        }
        return $next($request);
    }

}
