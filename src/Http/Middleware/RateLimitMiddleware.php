<?php

namespace TinyFramework\Http\Middleware;

use Closure;
use InvalidArgumentException;
use TinyFramework\Cache\CacheInterface;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;
use TinyFramework\RateLimiter\RateLimiter;

/**
 * @link https://datatracker.ietf.org/doc/html/draft-polli-ratelimit-headers-05
 */
class RateLimitMiddleware implements MiddlewareInterface
{

    public function __construct(
        protected CacheInterface $cache
    ) {
    }

    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        if (count($parameters) !== 2) {
            throw new InvalidArgumentException('Invalid argument count. 0=window, 1=limit');
        }

        $rateLimiter = new RateLimiter(
            $this->cache,
            hash('sha3-256', get_class($this)),
            intval($parameters[0]),
            intval($parameters[1])
        );

        $key = $this->getRequestSignature($request);
        $rateLimit = $rateLimiter->consume($key);
        if ($rateLimit->isAccepted()) {
            $response = $next($request);
            $response->header('RateLimit-Reset', sprintf('%d', $rateLimit->getRetryAt() - time()));
        } else {
            if ($request->wantsJson()) {
                $response = Response::json(['error' => 429, 'data' => 'You have exceeded your quota.',], 429);
            } else {
                $response = Response::view('errors.429', compact('rateLimit', 'request'), 429);
            }
            $response->header('Retry-After', sprintf('%d', $rateLimit->getRetryAt() - time()));
        }
        return $response->headers([
            'RateLimit-Limit' => sprintf('%d', $rateLimit->getLimit()),
            'RateLimit-Remaining' => sprintf('%s', $rateLimit->getRemainingTokens()),
        ]);
    }

    public function getRequestSignature(RequestInterface $request): string
    {
        return hash('sha3-256', $request->url()->host() . '|' . $request->realIp());
    }
}
