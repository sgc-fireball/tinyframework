<?php declare(strict_types=1);

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

    public function handle(Request $request, Closure $next, ...$parameters): Response
    {
        /** @var SessionInterface $session */
        $session = $this->container->get('session');
        $name = $this->container->get('config')->get('session.cookie');
        $session->open((string)$request->cookie($name));
        /** @var Response $response */
        $response = $next($request->session($session));
        $session->close();
        if ($name) {
            $response = $response->header('Set-Cookie', sprintf('%s=%s;', $name, $session->getId()));
        }
        return $response;
    }

}
