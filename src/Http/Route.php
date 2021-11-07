<?php declare(strict_types=1);

namespace TinyFramework\Http;

use Closure;

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
        if ($method === null) {
            return $this->method;
        }
        $this->method[] = strtoupper($method);
        return $this;
    }

    public function scheme(string $scheme = null): static|string
    {
        if ($scheme === null) {
            return $this->scheme;
        }
        $this->scheme = $scheme;
        return $this;
    }

    public function domain(string $domain = null): static|string
    {
        if ($domain === null) {
            return $this->domain;
        }
        $this->domain = $domain;
        return $this;
    }

    public function url(string $url = null): static|string
    {
        if ($url === null) {
            return $this->url;
        }
        $this->url = $url;
        return $this;
    }

    public function action(Closure|array|string|null $action = null): static|Closure|array|string|null
    {
        if ($action === null) {
            return $this->action;
        }
        $this->action = $action;
        return $this;
    }

    public function name(string $name = null): static|string|null
    {
        if ($name === null) {
            return $this->name;
        }
        $this->name = $name;
        return $this;
    }

    public function middleware(string|array|null $middlewares = null): static|array
    {
        if ($middlewares === null) {
            return $this->middleware;
        }
        $this->middleware = [];
        $middlewares = \is_array($middlewares) ? $middlewares : [$middlewares];
        foreach ($middlewares as $middleware) {
            if (!is_string($middleware)) {
                continue;
            }
            $class = $middleware;
            if (mb_strpos($middleware, ',') !== false) {
                list($class,) = explode(',', $middleware, 2);
            }
            if (!class_exists($class)) {
                throw new \RuntimeException('Invalid middleware found: ' . $class);
            }
            if (in_array($middleware, $this->middleware)) {
                continue;
            }
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    public function pattern(string $name = null, string $regex = null): static|array|string
    {
        if ($name !== null && $regex === null) {
            return \array_key_exists($name, $this->pattern) ? $this->pattern[$name] : $this->pattern['default'];
        }
        if ($name === null) {
            return $this->pattern;
        }
        $this->pattern[$name] = $regex;
        return $this;
    }

    public function parameter(string|array $key = null, mixed $value = null): static|array
    {
        if ($key === null) {
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
