<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use RuntimeException;
use TinyFramework\Auth\Authenticatable;
use TinyFramework\Helpers\IPv4;
use TinyFramework\Helpers\IPv6;
use TinyFramework\Helpers\Uuid;
use TinyFramework\Session\SessionInterface;

class Request implements RequestInterface
{
    public static array $trustedProxies = [
        '127.0.0.1',
        '::1',
    ];

    public static array $trustedHeaders = [
        'X-Real-IP',
        'X-Forwarded-For',
        'X-Forwarded-Proto',
        'X-Forwarded-Scheme',
        'X-Forwarded-Host',
        'X-Forwarded-Port',
    ];

    private string $id;

    private string $method = 'GET';

    private URL $url;

    private string $protocol = 'HTTP/1.0';

    private array $get = [];

    private array $post = [];

    private array $header = [];

    private array $server = [];

    private array $cookie = [];

    private array $files = [];

    private array $attributes = [];

    private ?Route $route = null;

    private ?SessionInterface $session = null;

    private ?Authenticatable $user = null;

    private ?string $body = null;

    private ?string $ip = null;

    private ?string $realIp = null;

    public static function setTrustedProxies(array $trustedProxies): string
    {
        self::$trustedProxies = $trustedProxies;
        return self::class;
    }

    public static function setTrustedHeaders(array $trustedHeaders): string
    {
        self::$trustedHeaders = $trustedHeaders;
        return self::class;
    }

    public static function factory(
        string $method,
        URL $url,
        array $get = [],
        array|string $post = [],
        array $headers = [],
        array $cookies = []
    ): Request {
        $request = new self();
        $request->realIp = $request->ip = $headers['REMOTE_ADDR'] ?? '127.0.0.1';
        $request->method = strtoupper($method);
        $request->url = new URL(preg_replace('/\?.*/', '', $url->__toString()));
        $request->protocol = 'HTTP/1.0';
        parse_str((string)$url->query(), $request->get);
        $request->get = array_merge($request->get, $get);
        $request->post = is_array($post) ? $post : [];
        $request->cookie = $cookies;
        $request->header = array_combine(
            array_map(
                function (string $key): string {
                    return mb_strtolower(str_replace('-', '_', $key));
                },
                array_keys($headers)
            ),
            array_map(
                function (array|string $value): array {
                    return is_array($value) ? array_values($value) : [$value];
                },
                array_values($headers)
            )
        );
        $request->body = is_string($post) ? $post : null;
        return self::compileTrustedProxies($request);
    }

    public static function fromGlobal(): Request
    {
        $request = new self();
        $request->realIp = $request->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $request->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $request->url = new URL(
            sprintf(
                '%s://%s%s:%d%s',
                to_bool($_SERVER['HTTPS'] ?? 'off') ? 'https' : 'http',
                \array_key_exists('REMOTE_USER', $_SERVER) ? $_SERVER['REMOTE_USER'] . '@:' : '',
                $_SERVER['HTTP_HOST'] ?? 'localhost',
                $_SERVER['SERVER_PORT'] ?? 80,
                $_SERVER['REQUEST_URI'] ?? '/'
            )
        );
        $request->protocol = $_SERVER['HTTP_SERVER_PROTOCOL'] ?? 'HTTP/1.0';
        $request->get = $_GET ?: [];
        $request->post = $_POST ?: [];
        if (\array_key_exists('HTTP_CONTENT_TYPE', $_SERVER)
            && (
                str_contains($_SERVER['HTTP_CONTENT_TYPE'], 'application/json')
                || str_contains($_SERVER['HTTP_CONTENT_TYPE'], 'application/problem+json')
                || str_contains($_SERVER['HTTP_CONTENT_TYPE'], 'application/csp-report')
            )
        ) {
            $request->post = json_decode(file_get_contents('php://input'), true);
        }
        $request->cookie = $_COOKIE ?: [];
        self::migrateFiles($_FILES ?: [], $request->files);
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                if ($key === 'HTTP_AUTHORIZATION') {
                    [$user, $pw] = explode(':', base64_decode(mb_substr($_SERVER['HTTP_AUTHORIZATION'], 6), true));
                    $request->header['php_auth_user'] = $user ?: null;
                    $request->header['php_auth_pass'] = $pw ?: null;
                }
                $key = mb_strtolower(str_replace('HTTP_', '', $key));
                $request->header[$key] = $request->header[$key] ?? [];
                $request->header[$key][] = $value;
            } else {
                $key = mb_strtolower($key);
                $request->server[$key] = $request->server[$key] ?? [];
                $request->server[$key][] = $value;
            }
        }
        if (\array_key_exists('_method', $request->get)) {
            $request->method = strtoupper($request->get['_method'] ?: $request->method);
            unset($request->get['_method']);
        }
        if (\array_key_exists('_method', $request->post)) {
            $request->method = strtoupper($request->post['_method'] ?: $request->method);
            unset($request->post['_method']);
        }
        $request->body = (string)file_get_contents("php://input");
        if (!preg_match('/^[A-Z]++$/D', $request->method)) {
            throw new RuntimeException(sprintf('Invalid method override "%s".', $request->method));
        }
        return self::compileTrustedProxies($request);
    }

    public static function fromSwoole(\Swoole\Http\Request $swoole, \Swoole\Websocket\Server $server): Request
    {
        if (!$swoole->isCompleted()) {
            throw new \RuntimeException('Received an incomplete Swoole Request.');
        }
        $request = new Request();
        $request->ip = $swoole->server['remote_addr'] ?? '127.0.0.1';
        $request->realIp = $request->ip;
        $request->method = $swoole->server['request_method'] ?? 'GET';
        $request->url = new URL(
            sprintf(
                '%s://%s%s:%d%s',
                $swoole->header['scheme'] ?? $swoole->header['x-forwarded-proto'] ?? 'http',
                \array_key_exists('remote_user', $swoole->header) ? $swoole->header['remote_user'] . '@:' : '',
                $swoole->header['host'] ?? $swoole->header['x-forwarded-host'] ?? 'localhost',
                $swoole->header['server_port'] ?? $swoole->header['x-forwarded-port'] ?? 80,
                $swoole->server['request_uri'] ?? '/'
            )
        );
        $request->protocol = $swoole->server['server_protocol'] ?? 'HTTP/1.0';
        $request->get = $swoole->get ?? [];
        $request->post = $swoole->post ?? [];
        $request->cookie = $swoole->cookie ?? [];
        self::migrateFiles($swoole->files ?: [], $request->files);
        $request->attributes = ['swoole_fd' => $swoole->fd];
        foreach ($swoole->header as $key => $value) {
            $key = mb_strtolower(str_replace('-', '_', $key));
            $request->header[$key] = [$value];
        }
        foreach ($swoole->server as $key => $value) {
            $key = mb_strtolower(str_replace('-', '_', $key));
            $request->server[$key] = [$value];
        }
        $request->body = $swoole->rawContent() ?: null;
        if (\array_key_exists('_method', $request->get)) {
            $request->method = strtoupper($request->get['_method'] ?: $request->method);
            unset($request->get['_method']);
        }
        if (\array_key_exists('_method', $request->post)) {
            $request->method = strtoupper($request->post['_method'] ?: $request->method);
            unset($request->post['_method']);
        }
        if (\in_array(
            $request->method,
            ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE', 'WEBSOCKET'],
            true
        )) {
            return self::compileTrustedProxies($request);
        }
        if (!preg_match('/^[A-Z]++$/D', $request->method)) {
            throw new RuntimeException(sprintf('Invalid method override "%s".', $request->method));
        }
        return self::compileTrustedProxies($request);
    }

    public static function compileTrustedProxies(Request $request): Request
    {
        $trusted = false;
        foreach (self::$trustedProxies as $trustedProxy) {
            if ($request->ip === $trustedProxy) {
                $trusted = true;
                break;
            }
            if (strpos($trustedProxy, '/') !== false) {
                $ip = null;
                if (filter_var($trustedProxy, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip = new IPv4($request->ip);
                } elseif (filter_var($trustedProxy, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $ip = new IPv6($request->ip);
                }
                if ($ip?->isIpInSubnet($request->ip)) {
                    $trusted = true;
                    break;
                }
            }
        }
        if (!$trusted) {
            return $request;
        }
        if (in_array('X-Real-IP', self::$trustedHeaders)) {
            $ip = $request->header('X-Forwarded-For');
            if (count($ip) === 1) {
                $request->realIp = $ip[0];
            }
        }
        if (in_array('X-Forwarded-For', self::$trustedHeaders)) {
            $for = $request->header('X-Forwarded-For');
            if (count($for) === 1) {
                $fors = explode(',', $for[0]);
                if ($ip = trim(array_shift($fors), ', ')) {
                    $request->realIp = $ip;
                }
            }
        }
        if (in_array('X-Forwarded-Proto', self::$trustedHeaders)) {
            $proto = $request->header('X-Forwarded-Proto');
            if (count($proto) === 1) {
                $request->url->scheme($proto[0]);
            }
        }
        if (in_array('X-Forwarded-Scheme', self::$trustedHeaders)) {
            $scheme = $request->header('X-Forwarded-Scheme');
            if (count($scheme) === 1) {
                $request->url->scheme($scheme[0]);
            }
        }
        if (in_array('X-Forwarded-Host', self::$trustedHeaders)) {
            $hosts = $request->header('X-Forwarded-Host');
            if (count($hosts) === 1) {
                $request->url->host($hosts[0]);
            }
        }
        if (in_array('X-Forwarded-Port', self::$trustedHeaders)) {
            $proto = $request->header('X-Forwarded-Port');
            if (count($proto) === 1) {
                $request->url->port((int)$proto[0]);
            }
        }
        return $request;
    }

    public function __construct()
    {
        $this->id = Uuid::v6();
        $this->url = new URL();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function get(string|array|null $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->get;
        }
        if (\is_array($key)) {
            foreach ($key as $k => $value) {
                $this->get[$k] = $value;
            }
            return $this;
        }
        if ($value === null) {
            return $this->get[$key] ?? null;
        }
        $this->get[$key] = $value;
        return $this;
    }

    public function attribute(string|array|null $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->attributes;
        }
        if (\is_array($key)) {
            foreach ($key as $k => $value) {
                $this->attributes[$k] = $value;
            }
            return $this;
        }
        if ($value === null) {
            return $this->attributes[$key] ?? null;
        }
        $this->attributes[$key] = $value;
        return $this;
    }

    public function post(string|array|null $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }
        if (\is_array($key)) {
            foreach ($key as $k => $value) {
                $this->post[$k] = $value;
            }
            return $this;
        }
        if ($value === null) {
            return $this->post[$key] ?? null;
        }
        $this->post[$key] = $value;
        return $this;
    }

    public function cookie(string|array|null $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->cookie;
        }
        if (\is_array($key)) {
            foreach ($key as $k => $value) {
                $this->cookie[$k] = $value;
            }
            return $this;
        }
        if ($value === null) {
            return $this->cookie[$key] ?? null;
        }
        $this->cookie[$key] = $value;
        return $this;
    }

    public function file(string|array|null $key = null, mixed $value = null): mixed
    {
        if ($key === null) {
            return $this->files;
        }
        if (\is_array($key)) {
            foreach ($key as $k => $value) {
                $this->files[$k] = $value;
            }
            return $this;
        }
        if ($value === null) {
            return $this->files[$key] ?? null;
        }
        $this->files[$key] = $value;
        return $this;
    }

    public function route(Route $route = null): static|Route|null
    {
        if ($route === null) {
            return $this->route;
        }
        $this->route = $route;
        return $this;
    }

    public function session(SessionInterface $session = null): static|SessionInterface|null
    {
        if ($session === null) {
            return $this->session;
        }
        $this->session = $session;
        return $this;
    }

    public function user(Authenticatable $user = null): Authenticatable|null|Request
    {
        if ($user !== null) {
            $this->user = $user;
            return $this;
        }
        return $this->user;
    }

    private function clone(): Request
    {
        $request = new self();
        $request->method = $this->method;
        $request->ip = $this->ip;
        $request->realIp = $this->realIp;
        $request->id = $this->id;
        $request->url = $this->url;
        $request->get = $this->get;
        $request->post = $this->post;
        $request->header = $this->header;
        $request->server = $this->server;
        $request->cookie = $this->cookie;
        $request->files = $this->files;
        $request->attributes = $this->attributes;
        $request->route = $this->route;
        $request->session = $this->session;
        $request->user = $this->user;
        return $request;
    }

    public function method(string $method = null): Request|string
    {
        if ($method === null) {
            return $this->method;
        }
        $request = $this->clone();
        $request->method = $method;
        return $request;
    }

    public function url(URL $url = null, bool $preserveHost = false): URL|Request
    {
        if ($url === null) {
            return $this->url;
        }
        if ($preserveHost) {
            $url->host((string)$this->url->host());
        }
        $request = $this->clone();
        $request->url = $url;
        return $request;
    }

    public function protocol(string $protocol = null): Request|string
    {
        if ($protocol === null) {
            return $this->protocol;
        }
        $request = $this->clone();
        $request->protocol = $protocol;
        return $request;
    }

    public function header(string $key = null, mixed $value = null): Request|array|string
    {
        if ($key === null) {
            return $this->header;
        }
        $key = mb_strtolower(str_replace('-', '_', $key));
        if ($value === null) {
            return $this->header[$key] ?? [];
        }
        $request = $this->clone();
        $request->header[$key] = is_array($value) ? $value : [$value];
        return $request;
    }

    public function server(string $key = null, mixed $value = null): Request|array|string
    {
        if ($key === null) {
            return $this->server;
        }
        $key = mb_strtolower(str_replace('-', '_', $key));
        if ($value === null) {
            return $this->server[$key] ?? [];
        }
        $request = $this->clone();
        $request->server[$key] = [$value];
        return $request;
    }

    public function body(string $body = null): Request|string|null
    {
        if ($body === null) {
            return $this->body;
        }
        $request = $this->clone();
        $request->body = $body;
        return $request;
    }

    public function json(mixed $json = null): Request|array|null
    {
        if ($json === null) {
            return json_decode($this->body, true) ?? [];
        }
        $request = $this->clone();
        $request->body = json_encode($json);
        return $request;
    }

    public function ip(string $ip = null): static|string|null
    {
        if ($ip === null) {
            return $this->ip;
        }
        $this->ip = $ip;
        return $this;
    }

    public function realIp(string $realIp = null): static|string|null
    {
        if ($realIp === null) {
            return $this->realIp;
        }
        $this->realIp = $realIp;
        return $this;
    }

    public function expectJson(): bool
    {
        $accept = explode(',', (string)$this->header('accept'));
        $accept = array_filter(
            $accept,
            fn($line) => str_starts_with(trim((string)$line), 'application/json')
                || str_starts_with(trim((string)$line), 'application/problem+json')
        );
        return count($accept) >= 1;
    }

    public function wantsJson(): bool
    {
        return $this->expectJson();
    }

    public function isAjax(): bool
    {
        return $this->header('x-requested-with') === 'XMLHttpRequest';
    }

    private static function migrateFiles(array $files, array &$results): void
    {
        foreach ($files as $key => $file) {
            if (!is_array($file)) {
                continue;
            }

            if (
                \array_key_exists('name', $file)
                && \array_key_exists('type', $file)
                && \array_key_exists('size', $file)
                && \array_key_exists('tmp_name', $file)
                && \array_key_exists('error', $file)
            ) {
                $results[$key] = new UploadedFile($file);
            } else {
                $results[$key] = [];
                self::migrateFiles($file, $results[$key]);
            }
        }
    }
}
