<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use Throwable;
use TinyFramework\Core\Kernel;
use TinyFramework\Core\Pipeline;
use TinyFramework\Exception\HttpException;
use TinyFramework\Template\Blade;

class HttpKernel extends Kernel implements HttpKernelInterface
{
    /** @var Closure[] */
    private array $terminateCallbacks = [];

    /** @var Closure[] */
    private array $terminateRequestCallbacks = [];

    private ?RequestInterface $request = null;

    public function handle(RequestInterface $request): Response
    {
        if (defined('SWOOLE') && SWOOLE) {
            $this->stopWatch = $this->resetStopWatch(microtime(true));
        }
        $this->stopWatch->start('router', 'kernel');
        try {
            $this->request = $request;
            $response = null;
            $this->container
                ->alias('request', RequestInterface::class)
                ->alias(Request::class, RequestInterface::class)
                ->singleton(RequestInterface::class, $request);
            if ($request->method() === 'OPTIONS') {
                $this->stopWatch->stop('router');
                $response = $this->getResponseByOptionsRequest($request);
            } elseif ($route = $this->container->get('router')->resolve($request)) {
                $this->stopWatch->stop('router');
                $response = $this->callRoute($route, $request);
            }
            if (!$response) {
                throw new HttpException('Page ' . $request->url()->__toString() . ' not found!', 404);
            }
        } catch (Throwable $e) {
            $response = $this->throwableToResponse($e);
            $this->container->get('logger')->error(
                exception2text($e),
                [
                    'request_id' => $request->id(),
                    'response_id' => $response->id(),
                    'exception' => $e,
                ]
            );
        }
        if (($origins = $request->header('Origin')) || $request->method() === 'OPTIONS') {
            $origin = \is_array($origins) && \array_key_exists(0, $origins) ? $origins[0] : null;
            if (\in_array('*', config('cors.allow_origins'))) {
                $response->header('Access-Control-Allow-Origin', '*');
            } elseif (\in_array($origin, config('cors.allow_origins'))) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                $response->header('Access-Control-Allow-Origin', rtrim(url('/'), '/'));
            }
        }

        if ($this->container->get('config')->get('app.env') !== 'production') {
            $stopWatchEvents = $this->stopWatch->section('main')->events();
            $serverTimingEvents = [];
            $count = 0;
            foreach ($stopWatchEvents as $name => $stopWatchEvent) {
                $serverTimingEvents[] = sprintf(
                    '%d-%s;desc="%d-%s";dur=%.2f',
                    $count,
                    $name,
                    $count++,
                    $name,
                    $stopWatchEvent->duration() * 1000
                );
            }
            $serverTimingEvents[] = sprintf('total;desc="total";dur=%.2f', $this->stopWatch->duration() * 1000);
            $response->header('Server-Timing', implode(',', $serverTimingEvents));
        }
        return $response->header('X-Response-ID', $response->id());
    }

    public function handleException(Throwable $e): int
    {
        self::$reservedMemory = null; // free 10kb ram
        $response = $this->throwableToResponse($e);
        $response
            ->header('X-Response-ID', $response->id())
            ->send();
        if ($this->request) {
            $this->terminateRequest($this->request, $response);
        }
        $this->terminate();
        die();
    }

    public function throwableToResponse(Throwable $e): Response
    {
        $this->stopWatch->start('controller.error', 'kernel');
        $statusCode = $e instanceof HttpException ? $e->getCode() : 500;
        $statusCode = ($statusCode < 400 || $statusCode > 599) ? 500 : $statusCode;
        $response = Response::error($statusCode);
        if ($this->container->has('blade')) {
            $view = $this->container->get('blade');
            assert($view instanceof Blade);
            if ($view->exists('errors.' . $statusCode)) {
                $response->content($view->render('errors.' . $statusCode, compact('e', 'response')));
            }
        }
        $this->stopWatch->stop('controller.error');
        return $response;
    }

    private function getResponseByOptionsRequest(RequestInterface $request): Response
    {
        $response = Response::new(null, 200);
        $router = $this->container->get('router');
        assert($router instanceof Router);
        $response->header('allow', implode(', ', $router->getAllowedMethodsByRequest($request)));

        if ($request->header('Origin')) {
            if ($methods = $request->header('Access-Control-Request-Method')) {
                $response->header('Access-Control-Allow-Method', implode(', ', $methods));
            }
            if ($headers = $request->header('Access-Control-Request-Headers')) {
                $response->header('Access-Control-Allow-Headers', implode(', ', $headers));
                $response->header('Access-Control-Expose-Headers', implode(', ', $headers));
            }
            $response->header('Access-Control-Max-Age', (string)config('cors.max_age'));
            $response->header(
                'Access-Control-Allow-Credentials',
                to_bool(config('cors.allow_credentials')) ? 'true' : 'false'
            );
        }
        return $response;
    }

    private function callRoute(Route $route, RequestInterface $request): Response
    {
        $request->route($route);
        $middlewares = $route->middleware();
        $onion = new Pipeline();
        $this->stopWatch->start('middleware', 'kernel');
        foreach ($middlewares as $middleware) {
            $onion->layers(function (RequestInterface $request, Closure $next) use ($middleware): Response {
                $parameters = [$request, $next];
                if (mb_strpos($middleware, ',') !== false) {
                    $additionalParameters = explode(',', $middleware);
                    $middleware = array_shift($additionalParameters);
                    $parameters = array_merge($parameters, $additionalParameters);
                }
                return $this->container->call($middleware . '@handle', $parameters);
            });
        }
        $this->stopWatch->stop('middleware');
        $this->stopWatch->start('controller', 'kernel');
        $response = $onion->call(function (RequestInterface $request): Response {
            $response = $this->container->call($request->route()->action(), $request->route()->parameter());
            if (!($response instanceof Response)) {
                $response = Response::new($response);
            }
            return $response;
        }, $request);
        if ($request->method() === 'HEAD') {
            $response->content('');
        }
        $this->stopWatch->stop('controller');
        return $response;
    }

    public function terminateRequest(RequestInterface $request, Response $response): static
    {
        foreach ($this->terminateRequestCallbacks as $callback) {
            try {
                $this->container->call($callback, ['request' => $request, 'response' => $response]);
            } catch (Throwable $e) {
                $this->container->get('logger')->error(exception2text($e));
            }
        }
        $this->request = null;
        return $this;
    }

    public function terminate(): static
    {
        foreach ($this->terminateCallbacks as $callback) {
            try {
                $this->container->call($callback);
            } catch (Throwable $e) {
                $this->container->get('logger')->error(exception2text($e));
            }
        }
        return $this;
    }

    public function terminateCallback(Closure $closure): static
    {
        $this->terminateCallbacks[] = $closure;
        return $this;
    }

    public function terminateRequestCallback(Closure $closure): static
    {
        $this->terminateRequestCallbacks[] = $closure;
        return $this;
    }
}
