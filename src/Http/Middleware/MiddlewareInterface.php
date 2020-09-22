<?php declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use TinyFramework\Http\Request;
use Closure;
use TinyFramework\Http\Response;

interface MiddlewareInterface
{

    public function handle(Request $request, Closure $next, ...$parameters): Response;

}
