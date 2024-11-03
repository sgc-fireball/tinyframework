<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;
use TinyFramework\Session\SessionInterface;

class CsrfMiddlewaere implements MiddlewareInterface
{
    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        $session = $request->session();
        if (!($session instanceof SessionInterface)) {
            return Response::error(419);
        }

        $tokens = $session->get('csrf-token');
        if (!$tokens) {
            $session->set('csrf-token', $tokens = bin2hex(random_bytes(32)));
        }
        if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            $given = $request->post('csrf-token')
                ?? $request->header('x-csrf-token')[0]
                ?? null;
            if ($tokens !== $given) {
                return Response::error(419);
            }
        }

        return $next($request);
    }
}
