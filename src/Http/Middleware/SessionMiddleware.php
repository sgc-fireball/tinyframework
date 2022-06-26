<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Http\Request;
use TinyFramework\Http\Response;
use TinyFramework\Session\SessionInterface;

class SessionMiddleware implements MiddlewareInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request, Closure $next, mixed ...$parameters): Response
    {
        $session = $this->container->get('session');
        assert($session instanceof SessionInterface);
        $name = $this->container->get('config')->get('session.cookie');
        if ($name) {
            $session->open((string)$request->cookie($name));
            $response = $next($request->session($session));
            assert($response instanceof Response);
            $session->close();
            $response->cookie(
                $name,
                $session->getId(),
                0,
                '',
                $request->url()->host(),
                ($request->server('https')[0] ?? 'off') === 'on',
                true
            );
        } else {
            $response = $next($request);
            assert($response instanceof Response);
        }
        return $response;
    }
}
