<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use RuntimeException;
use TinyFramework\Http\Request;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;

class ThrottleMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        $maxRequests = array_key_exists(0, $parameters) ? (int)$parameters[0] : 60;
        $timeInterval = array_key_exists(0, $parameters) ? (int)$parameters[0] : 60;
        $key = 'throttle:' . $this->getRequestSignature($request);
        cache()->set($key, $current = (cache()->get($key) ?? 0) + 1, $timeInterval);
        if ($current <= $maxRequests) {
            return $next($request);
        }
        throw new RuntimeException('', 429);
    }

    public function getRequestSignature(RequestInterface $request): string
    {
        return hash('sha3-256', $request->url()->host() . '|' . $request->realIp());
    }
}
