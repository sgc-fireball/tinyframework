<?php declare(strict_types=1);

namespace TinyFramework\Http;

use TinyFramework\Http\Middleware\MiddlewareInterface;

class Route
{

    private array $method = [];

    private string $scheme = 'https?';

    private string $domain = '.*';

    private string $uri = '/';

    /** @var array|string|null */
    private $action = null;

    private ?string $name = null;

    private array $middleware = [];

    private array $parameter = [];

    private array $pattern = ['default' => '[^\/]+'];

    private array $attributes = [];

    /**
     * @param string|null $method
     * @return $this|array
     */
    public function method(string $method = null)
    {
        if (is_null($method)) {
            return $this->method;
        }
        $this->method[] = strtoupper($method);
        return $this;
    }

    /**
     * @param string|null $scheme
     * @return $this|string
     */
    public function scheme(string $scheme = null)
    {
        if (is_null($scheme)) {
            return $this->scheme;
        }
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @param string|null $domain
     * @return $this|string
     */
    public function domain(string $domain = null)
    {
        if (is_null($domain)) {
            return $this->domain;
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param string|null $uri
     * @return $this|string
     */
    public function uri(string $uri = null)
    {
        if (is_null($uri)) {
            return $this->uri;
        }
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param null $action
     * @return $this|array|string|null
     */
    public function action($action = null)
    {
        if (is_null($action)) {
            return $this->action;
        }
        $this->action = $action;
        return $this;
    }

    /**
     * @param string|null $name
     * @return $this|string|null
     */
    public function name(string $name = null)
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
    public function middleware($middlewares = null)
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

    /**
     * @param string|null $name
     * @param string|null $regex
     * @return $this|array|string|string[]
     */
    public function pattern(string $name = null, string $regex = null)
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

    /**
     * @param null $key
     * @param null $value
     * @return $this|array
     */
    public function parameter($key = null, $value = null)
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
