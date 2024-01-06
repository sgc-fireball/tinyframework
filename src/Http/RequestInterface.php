<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use RuntimeException;
use TinyFramework\Auth\Authenticatable;
use TinyFramework\Helpers\IPv4;
use TinyFramework\Helpers\IPv6;
use TinyFramework\Session\SessionInterface;

interface RequestInterface
{

    public function id(): string;

    public function get(string|array|null $key = null, mixed $value = null): mixed;

    public function attribute(string|array|null $key = null, mixed $value = null): mixed;

    public function post(string|array|null $key = null, mixed $value = null): mixed;

    public function cookie(string|array|null $key = null, mixed $value = null): mixed;

    public function file(string|array|null $key = null, mixed $value = null): mixed;

    public function route(Route $route = null): static|Route|null;

    public function session(SessionInterface $session = null): static|SessionInterface|null;

    public function user(Authenticatable $user = null): Authenticatable|null|RequestInterface;

    public function method(string $method = null): RequestInterface|string;

    public function url(URL $url = null, bool $preserveHost = false): URL|RequestInterface;

    public function protocol(string $protocol = null): RequestInterface|string;

    public function header(string $key = null, mixed $value = null): RequestInterface|array|string;

    public function server(string $key = null, mixed $value = null): RequestInterface|array|string;

    public function body(string $body = null): RequestInterface|string|null;

    public function json(mixed $json = null): RequestInterface|array|null;

    public function ip(string $ip = null): static|string|null;

    public function realIp(string $realIp = null): static|string|null;

    public function expectJson(): bool;

    public function wantsJson(): bool;

    public function isAjax(): bool;

}
