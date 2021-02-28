<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Core\ContainerInterface;
use TinyFramework\Http\Middleware\MaintenanceMiddleware;

class Router
{

    protected ContainerInterface $container;

    private array $routes = [];

    private array $pattern = ['default' => '[^\/]+'];

    private array $bindings = [];

    private array $middleware = [
        MaintenanceMiddleware::class
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(): self
    {
        $router = $this;
        $files = ['config', 'api', 'web'];
        foreach ($files as $file) {
            $file = sprintf('routes/%s.php', $file);
            if (file_exists($file)) {
                require_once $file;
            }
        }
        return $this;
    }

    public function pattern(string $name = null, string $regex = null): Router|array|string
    {
        if (!is_null($name) && is_null($regex)) {
            return array_key_exists($name, $this->pattern) ? $this->pattern[$name] : $this->pattern['default'];
        }
        if (is_null($name)) {
            return $this->pattern;
        }
        $this->pattern[$name] = $regex;
        return $this;
    }

    public function bind(string $name, Closure $closure = null): Router|callable|null
    {
        if (is_null($closure)) {
            if (array_key_exists($name, $this->bindings)) {
                return $this->bindings[$name];
            }
            return null;
        }
        $this->bindings[$name] = $closure;
        return $this;
    }

    public function middleware($middleware = null): Router|array
    {
        if (is_null($middleware)) {
            return $this->middleware;
        }
        $middleware = is_array($middleware) ? $middleware : [$middleware];
        foreach ($middleware as $m) {
            $this->middleware[] = $m;
        }
        return $this;
    }

    public function group(array $options, Closure $inner): self
    {
        $options['prefix'] = $options['prefix'] ?? '';
        $router = new Router($this->container);
        $router->middleware($options['middleware'] ?? []);
        $inner($router);
        /** @var Route $route */
        foreach ($router->routes as $route) {
            $route->uri($options['prefix'] . $route->uri());
            if (array_key_exists('scheme', $options)) {
                $route->scheme($options['scheme']);
            }
            if (array_key_exists('domain', $options)) {
                $route->domain($options['domain']);
            }
            $route->middleware(array_merge($router->middleware, $route->middleware()));
            $this->routes[] = $route;
        }
        return $this;
    }

    public function any(string $uri, $action): Route
    {
        $route = (new Route())->method('ANY')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function get(string $uri, $action): Route
    {
        $route = (new Route())->method('GET')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function head(string $uri, $action): Route
    {
        $route = (new Route())->method('head')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function post(string $uri, $action): Route
    {
        $route = (new Route())->method('POST')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function put(string $uri, $action): Route
    {
        $route = (new Route())->method('PUT')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function delete(string $uri, $action): Route
    {
        $route = (new Route())->method('DELETE')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function connect(string $uri, $action): Route
    {
        $route = (new Route())->method('CONNECT')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function options(string $uri, $action): Route
    {
        $route = (new Route())->method('OPTIONS')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function patch(string $uri, $action): Route
    {
        $route = (new Route())->method('PATCH')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function purge(string $uri, $action): Route
    {
        $route = (new Route())->method('PURGE')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function trace(string $uri, $action): Route
    {
        $route = (new Route())->method('TRACE')->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function custom(string $method, string $uri, $action): Route
    {
        $route = (new Route())->method($method)->uri($uri)->action($action);
        $this->routes[] = $route;
        return $route;
    }

    public function fallback($action): Route
    {
        $route = (new Route())->method('any')->uri('.*')->action($action)->name('fallback');
        $this->routes[] = $route;
        return $route;
    }

    public function resolve(Request $request): ?Route
    {
        $url = $request->uri()->query([])->fragment('')->__toString();

        /** @var Route $route */
        foreach ($this->routes as $route) {
            $allowedMethods = $route->method();
            if (!in_array($request->method(), $allowedMethods) && !in_array('ANY', $allowedMethods)) {
                continue;
            }
            $regex = $this->translateUri($route);
            if (preg_match($regex, $url, $match)) {
                $match = array_filter($match, function ($value, $key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_BOTH);
                foreach ($match as $name => &$value) {
                    if ($callback = $this->bind($name)) {
                        if ($callback instanceOf Closure && $newValue = $callback($value)) {
                            $value = $newValue;
                            continue;
                        }
                        continue 2;
                    }
                }
                $route->parameter($match);
                return $route;
            }
        }
        return null;
    }

    protected function translateUri(Route $route): string
    {
        $patterns = array_merge($this->pattern(), $route->pattern());
        $uri = $route->uri();
        preg_match_all('/\{([a-zA-Z0-9]+)\}/m', $uri, $matches, PREG_SET_ORDER);
        $matches = array_map(function ($value) {
            return $value[1];
        }, $matches);
        foreach ($matches as $name) {
            $pattern = sprintf('(?<%s>%s)', $name, $patterns[$name] ?? $patterns['default']);
            $uri = str_replace('{' . $name . '}', $pattern, $uri);
        }
        $regex = '#^';
        $regex .= sprintf('(?<_scheme>%s)', $route->scheme());
        $regex .= '://';
        $regex .= sprintf('(?<_domain>%s)', $route->domain());
        $regex .= '/';
        $regex .= sprintf('(?<_uri>%s)', $uri);
        $regex .= '$#i';
        return $regex;
    }

    public function path(string $name, array $parameters = []): string
    {
        /** @var Route $route */
        foreach ($this->routes as $route) {
            if ($name !== $route->name()) {
                continue;
            }
            $url = $route->uri();
            foreach ($parameters as $key => $value) {
                $value = is_object($value) && method_exists($value, '__toString') ? $value->__toString() : $value;
                $value = is_bool($value) ? ($value ? 'TRUE' : 'FALSE') : $value;
                $value = is_null($value) ? 'NULL' : $value;
                $url = str_replace('{' . $key . '}', (string)$value, $url);
            }
            if (mb_strpos($url, '{') || strpos($url, '}')) {
                throw new \RuntimeException('Missing parameters.');
            }
            return '/' . ltrim($url, '/');
        }
        throw new \RuntimeException('Could not found route.');
    }

}
