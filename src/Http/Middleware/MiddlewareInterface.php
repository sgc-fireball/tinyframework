<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next, mixed ...$parameters): Response;
}
