<?php

declare(strict_types=1);

namespace TinyFramework\Http\Middleware;

use Closure;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Helpers\Uuid;
use TinyFramework\Http\RequestInterface;
use TinyFramework\Http\Response;
use TinyFramework\Session\SessionInterface;

class SessionMiddleware implements MiddlewareInterface
{

    public const FLASH_MESSAGES = 'flash_messages';
    public const FLASH_ERRORS = 'flash_errors';
    public const FLASH_INPUTS = 'flash_inputs';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(RequestInterface $request, Closure $next, mixed ...$parameters): Response
    {
        $session = $this->container->get('session');
        assert($session instanceof SessionInterface);
        $name = $this->container->get('config')->get('session.cookie');
        if ($name) {
            $id = (string)$request->cookie($name) ?: Uuid::v4();
            $session->open($id);
            $request->attribute(self::FLASH_MESSAGES, $session->get(self::FLASH_MESSAGES) ?? []);
            $request->attribute(self::FLASH_ERRORS, $session->get(self::FLASH_ERRORS) ?? []);
            $request->attribute(self::FLASH_INPUTS, $session->get(self::FLASH_INPUTS) ?? []);
            $response = $next($request->session($session));
            assert($response instanceof Response);
            $session->set(self::FLASH_MESSAGES, $response->getFlashMessages());
            $session->set(self::FLASH_ERRORS, $response->getFlashErrors());
            $session->set(self::FLASH_INPUTS, $response->getFlashInputs());
            $session->close();
            $response->cookie(
                $name,
                $session->getId(),
                -1,
                '/',
                $request->url()->host(),
                to_bool($request->server('https')[0] ?? false),
                true,
                false,
                'Lax'
            );
        } else {
            $response = $next($request);
            assert($response instanceof Response);
        }
        return $response;
    }
}
