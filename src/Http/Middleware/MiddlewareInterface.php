<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;

interface MiddlewareInterface
{
    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response;
}
