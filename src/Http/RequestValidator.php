<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use TinyFramework\Auth\Authenticatable;
use TinyFramework\Session\SessionInterface;
use TinyFramework\Validation\Rule\RuleInterface;
use TinyFramework\Validation\ValidationException;
use TinyFramework\Validation\ValidatorInterface;

abstract class RequestValidator implements RequestInterface
{
    private array $safe = [];

    private array $errorBag = [];

    public function __construct(
        private ValidatorInterface $validator,
        private Request $request
    ) {
    }

    /**
     * @return array<string, RuleInterface|array|string>
     */
    abstract public function rules(): array;

    public function validate(): bool
    {
        try {
            $this->safe = $this->validator->validate($this->request, $this->rules());
            return true;
        } catch (ValidationException $e) {
            $this->errorBag = $e->getErrorBag();
            return false;
        }
    }

    public function safe(): array
    {
        return $this->safe;
    }

    public function getErrorBag(): array
    {
        return $this->errorBag;
    }

    public function id(): string
    {
        return $this->request->id();
    }

    public function get(array|string|null $key = null, mixed $value = null): mixed
    {
        return $this->request->get($key, $value);
    }

    public function attribute(array|string|null $key = null, mixed $value = null): mixed
    {
        return $this->request->attribute($key, $value);
    }

    public function post(array|string|null $key = null, mixed $value = null): mixed
    {
        return $this->request->post($key, $value);
    }

    public function cookie(array|string|null $key = null, mixed $value = null): mixed
    {
        return $this->request->cookie($key, $value);
    }

    public function file(array|string|null $key = null, mixed $value = null): mixed
    {
        return $this->request->file($key, $value);
    }

    public function route(Route $route = null): static|Route|null
    {
        return $this->request->route($route);
    }

    public function session(SessionInterface $session = null): static|SessionInterface|null
    {
        return $this->request->session($session);
    }

    public function user(Authenticatable $user = null): Authenticatable|null|RequestInterface
    {
        return $this->request->user($user);
    }

    public function method(string $method = null): RequestInterface|string
    {
        return $this->request->method($method);
    }

    public function url(URL $url = null, bool $preserveHost = false): URL|RequestInterface
    {
        return $this->request->url($url, $preserveHost);
    }

    public function protocol(string $protocol = null): RequestInterface|string
    {
        return $this->request->protocol($protocol);
    }

    public function header(string $key = null, mixed $value = null): RequestInterface|array|string
    {
        return $this->request->header($key, $value);
    }

    public function server(string $key = null, mixed $value = null): RequestInterface|array|string
    {
        return $this->request->server($key, $value);
    }

    public function body(string $body = null): RequestInterface|string|null
    {
        return $this->request->body($body);
    }

    public function json(mixed $json = null): RequestInterface|array|null
    {
        return $this->request->json($json);
    }

    public function ip(string $ip = null): static|string|null
    {
        return $this->request->ip($ip);
    }

    public function realIp(string $realIp = null): static|string|null
    {
        return $this->request->realIp($realIp);
    }

    public function expectJson(): bool
    {
        return $this->request->expectJson();
    }

    public function wantsJson(): bool
    {
        return $this->request->wantsJson();
    }

    public function isAjax(): bool
    {
        return $this->request->isAjax();
    }
}
