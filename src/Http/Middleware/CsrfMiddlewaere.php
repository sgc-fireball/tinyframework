<?php declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;

class CsrfMiddlewaere implements MiddlewareInterface
{

    public function handle(Request $request, Closure $next, mixed ...$parameters): Response
    {
        if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            $session = $request->session();
            if (!$session) {
                return Response::error(419);
            }
            $session->set('csrf-token', $session->get('csrf-token') ?? guid());
            $token = $request->get('csrf-token') ?? $request->post('csrf-token') ?? $request->header('x-csrf-token');
            if ($session->get('csrf-token') !== $token) {
                return Response::error(419);
            }
        }
        return $next($request);
    }

}
