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

    private ?Request $request;

    public function handle(Request $request): Response
    {
        try {
            $this->request = $request;
            $response = null;
            $this->container->alias('request', Request::class)->singleton(Request::class, $request);
            if ($route = $this->container->get('router')->resolve($request)) {
                $response = $this->callRoute($route, $request);
            }
            if (!$response) {
                throw new HttpException('Page not found! ' . $request->uri(), 404);
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
        $response->header('X-Request-ID', $request->id())->header('X-Response-ID', $response->id())->send();
        $this->terminate($request, $response);
        return $response;
    }

    public function handleException(\Throwable $e): int
    {
        $response = $this->throwableToResponse($e);
        $response
            ->header('X-Request-ID', $this->request ? $this->request->id() : '')
            ->header('X-Response-ID', $response->id())
            ->send();
        die();
    }

    private function throwableToResponse(\Throwable $e): Response
    {
        $statusCode = $e instanceof HttpException ? $e->getCode() : 500;
        $statusCode = ($statusCode < 400 || $statusCode > 599) ? 500 : $statusCode;
        $response = Response::error($statusCode);
        /** @var Blade $view */
        $view = $this->container->get('blade');
        if ($view->exists('errors.' . $statusCode)) {
            $response = Response::new('', $statusCode);
            $response->content($view->render('errors.' . $statusCode, compact('e', 'response')));
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

    private function terminate(Request $request, Response $response): self
    {
        foreach ($this->terminateCallbacks as $callback) {
            try {
                $this->container->call($callback, ['request' => $request, 'response' => $response]);
            } catch (\Throwable $e) {
                $this->container->get('logger')->error(exception2text($e));
            }
        }
        return $this;
    }

    public function terminateCallback(Closure $closure): self
    {
        $this->terminateCallbacks[] = $closure;
        return $this;
    }

}
