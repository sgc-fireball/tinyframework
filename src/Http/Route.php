<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Http\Middleware\MiddlewareInterface;

class Route
{

    private array $method = [];

    private string $scheme = 'https?';

    private string $domain = '.*';

    private string $uri = '/';

    private Closure|array|string|null $action = null;

    private ?string $name = null;

    private array $middleware = [];

    private array $parameter = [];

    private array $pattern = ['default' => '[^\/]+'];

    private array $attributes = [];

    public function method(string $method = null): Route|array
    {
        if (is_null($method)) {
            return $this->method;
        }
        $this->method[] = strtoupper($method);
        return $this;
    }

    public function scheme(string $scheme = null): Route|string
    {
        if (is_null($scheme)) {
            return $this->scheme;
        }
        $this->scheme = $scheme;
        return $this;
    }

    public function domain(string $domain = null): Route|string
    {
        if (is_null($domain)) {
            return $this->domain;
        }
        $this->domain = $domain;
        return $this;
    }

    public function uri(string $uri = null): Route|string
    {
        if (is_null($uri)) {
            return $this->uri;
        }
        $this->uri = $uri;
        return $this;
    }

    public function action(Closure|array|string|null $action = null): Route|Closure|array|string|null
    {
        if (is_null($action)) {
            return $this->action;
        }
        $this->action = $action;
        return $this;
    }

    public function name(string $name = null): Route|string|null
    {
        if (is_null($name)) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    /**
     * @param string|array|null $middlewares
     * @return $this|array
     */
    public function middleware($middlewares = null): Route|array
    {
        if (is_null($middlewares)) {
            return $this->middleware;
        }
        $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
        foreach ($middlewares as $middleware) {
            $class = $middleware;
            if (mb_strpos($middleware, ',') !== false) {
                list($class,) = explode(',', $middleware, 2);
            }
            if (class_exists($class)) {
                $this->middleware[] = $middleware;
            }
        }
        return $this;
    }

    public function pattern(string $name = null, string $regex = null): Route|array|string
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

    public function parameter(string|array $key = null, $value = null): Route|array
    {
        if (is_null($key)) {
            return $this->parameter;
        }
        if (!is_string($key)) {
            $this->parameter = $key;
            return $this;
        }
        $this->parameter[$key] = $value;
        return $this;
    }

}
