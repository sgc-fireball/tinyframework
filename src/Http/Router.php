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

    /**
     * @param string $name
     * @param string $class
     * @param array $options
     * @return array
     */
    public function resource(string $name, string $class, array $options = []): array
    {
        $options['url'] = $options['url'] ?? $name;
        $options['parameter'] = $options['parameter'] ?? $name;

        $methods = ['index', 'create', 'store', 'show', 'edit', 'update', 'delete'];
        if (isset($options['only'])) {
            $methods = array_intersect($methods, (array)$options['only']);
        }
        if (isset($options['except'])) {
            $methods = array_diff($methods, (array)$options['except']);
        }

        $options['names'] = $options['names'] ?? [];
        $options['names']['index'] = $options['names']['index'] ?? ($name . '.index');
        $options['names']['create'] = $options['names']['create'] ?? ($name . '.create');
        $options['names']['store'] = $options['names']['store'] ?? ($name . '.store');
        $options['names']['show'] = $options['names']['show'] ?? ($name . '.show');
        $options['names']['edit'] = $options['names']['edit'] ?? ($name . '.edit');
        $options['names']['update'] = $options['names']['update'] ?? ($name . '.update');
        $options['names']['delete'] = $options['names']['delete'] ?? ($name . '.delete');

        $list = [];
        if (in_array('index', $methods)) {
            $this->routes[] = $list['index'] = $this->get(
                $options['url'],
                $class . '@index'
            )->name($options['names']['index']);
        }
        if (in_array('create', $methods)) {
            $this->routes[] = $list['create'] = $this->get(
                $options['url'] . '/create',
                $class . '@create'
            )->name($options['names']['create']);
        }
        if (in_array('store', $methods)) {
            $this->routes[] = $list['store'] = $this->post(
                $options['url'],
                $class . '@store'
            )->name($options['names']['store']);
        }
        if (in_array('show', $methods)) {
            $this->routes[] = $list['show'] = $this->get(
                $options['url'] . '/{' . $options['parameter'] . '}',
                $class . '@show'
            )->name($options['names']['show']);
        }
        if (in_array('edit', $methods)) {
            $this->routes[] = $list['edit'] = $this->get(
                $options['url'] . '/{' . $options['parameter'] . '}/edit',
                $class . '@edit'
            )->name($options['names']['edit']);
        }
        if (in_array('update', $methods)) {
            $this->routes[] = $list['update'] = $this->put(
                $options['url'] . '/{' . $options['parameter'] . '}',
                $class . '@update'
            )->name($options['names']['update']);
            $this->routes[] = $list['update'] = $this->patch(
                $options['url'] . '/{' . $options['parameter'] . '}',
                $class . '@update'
            );
        }
        if (in_array('delete', $methods)) {
            $this->routes[] = $list['delete'] = $this->delete(
                $options['url'] . '/{' . $options['parameter'] . '}',
                $class . '@delete'
            )->name($options['names']['update']);
        }
        return $list;
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
                        if ($callback instanceof Closure && $newValue = $callback($value)) {
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
            return rtrim('/' . ltrim($url, '/'), '?&');
        }
        throw new \RuntimeException('Could not found route.');
    }

    public function url(string $path = '/', array $parameters = []): string
    {
        $url = '/' . ltrim($path, '/');
        $url .= mb_strpos($url, '?') === false ? '?' : '&';
        $url .= http_build_query($parameters);
        if ($request = $this->container->get('request')) {
            return (new Uri($url))
                ->scheme($request->uri()->scheme())
                ->host($request->uri()->host())
                ->port($request->uri()->port())
                ->__toString();
        }
        return rtrim($url, '?&');
    }

}
