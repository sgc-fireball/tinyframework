<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;
use TinyFramework\Http\Middleware\MiddlewareInterface;

class Route
{

    private array $method = [];

    private string $scheme = 'https?';

    private string $domain = '.*';

    private string $url = '/';

    private Closure|array|string|null $action = null;

    private ?string $name = null;

    private array $middleware = [];

    private array $parameter = [];

    private array $pattern = ['default' => '[^\/]+'];

    private array $attributes = [];

    public function method(string $method = null): static|array
    {
        if (is_null($method)) {
            return $this->method;
        }
        $this->method[] = strtoupper($method);
        return $this;
    }

    public function scheme(string $scheme = null): static|string
    {
        if (is_null($scheme)) {
            return $this->scheme;
        }
        $this->scheme = $scheme;
        return $this;
    }

    public function domain(string $domain = null): static|string
    {
        if (is_null($domain)) {
            return $this->domain;
        }
        $this->domain = $domain;
        return $this;
    }

    public function url(string $url = null): static|string
    {
        if (is_null($url)) {
            return $this->url;
        }
        $this->url = $url;
        return $this;
    }

    public function action(Closure|array|string|null $action = null): static|Closure|array|string|null
    {
        if (is_null($action)) {
            return $this->action;
        }
        $this->action = $action;
        return $this;
    }

    public function name(string $name = null): static|string|null
    {
        if (is_null($name)) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    public function middleware(string|array|null $middlewares = null): static|array
    {
        if (is_null($middlewares)) {
            return $this->middleware;
        }
        $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
        foreach ($middlewares as $middleware) {
            $class = $middleware;
            if (!is_string($middleware)) {
                continue;
            }
            if (mb_strpos($middleware, ',') !== false) {
                list($class,) = explode(',', $middleware, 2);
            }
            if (class_exists($class)) {
                $this->middleware[] = $middleware;
            }
        }
        return $this;
    }

    public function pattern(string $name = null, string $regex = null): static|array|string
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

    public function parameter(string|array $key = null, mixed $value = null): static|array
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
