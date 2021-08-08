<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Console\Output\OutputInterface;
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

    private ?Request $request = null;

    public function handle(Request $request): Response
    {
        try {
            $this->request = $request;
            $response = null;
            $this->container->alias('request', Request::class)->singleton(Request::class, $request);
            if ($request->method() === 'OPTIONS') {
                $response = $this->getResponseByOptionsRequest($request);
            } else if ($route = $this->container->get('router')->resolve($request)) {
                $response = $this->callRoute($route, $request);
            }
            if (!$response) {
                throw new HttpException('Page not found! ' . $request->url(), 404);
            }
        } catch (\Throwable $e) {
            $response = $this->throwableToResponse($e);
            $this->container->get('logger')->error(exception2text($e),
                [
                    'request_id' => $request->id(),
                    'response_id' => $response->id(),
                    'exception' => $e
                ]
            );
        }
        if (($origins = $request->header('Origin')) || $request->method() === 'OPTIONS') {
            $origin = is_array($origins) && array_key_exists(0, $origins) ? $origins[0] : null;
            if (in_array('*', config('cors.allow_origins'))) {
                $response->header('Access-Control-Allow-Origin', '*');
            } else if (in_array($origin, config('cors.allow_origins'))) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                $response->header('Access-Control-Allow-Origin', rtrim(url('/'), '/'));
            }
        }
        return $response
            ->header('X-Request-ID', $request->id())
            ->header('X-Response-ID', $response->id())
            ->header('X-Response-Duration', round(microtime(true) - TINYFRAMEWORK_START, 4) . ' sec.');
    }

    public function handleException(\Throwable $e): int
    {
        $response = $this->throwableToResponse($e);
        $response
            ->header('X-Request-ID', $this->request ? $this->request->id() : '')
            ->header('X-Response-ID', $response->id())
            ->header('X-Response-Duration', round(microtime(true) - TINYFRAMEWORK_START, 4) . ' sec.')
            ->send();
        if ($this->request) {
            $this->terminateRequest($this->request, $response);
        }
        $this->terminate();
        die();
    }

    private function throwableToResponse(\Throwable $e): Response
    {
        $statusCode = $e instanceof HttpException ? $e->getCode() : 500;
        $statusCode = ($statusCode < 400 || $statusCode > 599) ? 500 : $statusCode;
        $response = Response::error($statusCode);
        if (!$this->container->has('blade')) {
            $response = Response::new('', $statusCode);
        } else {
            /** @var Blade $view */
            $view = $this->container->get('blade');
            if ($view->exists('errors.' . $statusCode)) {
                $response = Response::new('', $statusCode);
                $response->content($view->render('errors.' . $statusCode, compact('e', 'response')));
            }
        }
        return $response;
    }

    private function getResponseByOptionsRequest(Request $request): Response
    {
        $response = Response::new(null, 200);
        /** @var Router $router */
        $router = $this->container->get('router');
        $response->header('allow', implode(', ', $router->getAllowedMethodsByRequest($request)));

        if ($request->header('Origin')) {
            if ($methods = $request->header('Access-Control-Request-Method')) {
                $response->header('Access-Control-Allow-Method', implode(', ', $methods));
            }
            if ($headers = $request->header('Access-Control-Request-Headers')) {
                $response->header('Access-Control-Allow-Headers', implode(', ', $headers));
            }
            $response->header('Access-Control-Max-Age', (string)config('cors.max_age'));
            $response->header('Access-Control-Allow-Credentials', to_bool(config('cors.allow_credentials')) ? 'true' : 'false');
        }
        return $response;
    }

    private function callRoute(Route $route, Request $request): Response
    {
        $request->route($route);
        $middlewares = array_merge($this->container->get('router')->middleware(), $route->middleware());
        $onion = new Pipeline();
        foreach ($middlewares as $middleware) {
            $onion->layers(function (Request $request, Closure $next) use ($middleware) {
                $parameters = [$request, $next];
                if (mb_strpos($middleware, ',') !== false) {
                    $additionalParameters = explode(',', $middleware);
                    $middleware = array_shift($additionalParameters);
                    $parameters = array_merge($parameters, $additionalParameters);
                }
                return $this->container->call($middleware . '@handle', $parameters);
            });
        }
        return $onion->call(function (Request $request) {
            $response = $this->container->call($request->route()->action(), $request->route()->parameter());
            if (!($response instanceof Response)) {
                $response = Response::new($response);
            }
            return $response;
        }, $request);
    }

    public function terminateRequest(Request $request, Response $response): static
    {
        foreach ($this->terminateRequestCallbacks as $callback) {
            try {
                $this->container->call($callback, ['request' => $request, 'response' => $response]);
            } catch (\Throwable $e) {
                $this->container->get('logger')->error(exception2text($e));
            }
        }
        $this->terminateRequestCallbacks = [];
        return $this;
    }

    public function terminate(): static
    {
        foreach ($this->terminateCallbacks as $callback) {
            try {
                $this->container->call($callback);
            } catch (\Throwable $e) {
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
