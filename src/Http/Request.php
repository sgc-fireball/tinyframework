<?php declare(strict_types=1);

namespace TinyFramework\Http;

use RuntimeException;
use TinyFramework\Session\SessionInterface;
use Swoole\Http\Request as SwooleRequest;

class Request
{

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

    private ?Route $route = null;

    private ?SessionInterface $session = null;

    private mixed $user = null;

    private ?string $body = null;

    private ?string $ip = null;

    public static function fromSwooleRequest(SwooleRequest $req): Request
    {
        /** @see https://www.swoole.co.uk/docs/modules/swoole-http-request */
        $request = new self();
        $request->ip = $req->server['remote_addr'];
        $request->method = strtoupper($req->getMethod());
        $request->url = new URL(sprintf(
            '%s://%s%s%s',
            to_bool($req->server['https'] ?? 'off') ? 'https' : 'http',
            array_key_exists('remote_user', $req->server) ? $req->server['remote_user'] . '@' : '',
            $req->header['host'] ?? 'localhost',
            $req->server['request_uri'] ?? '/'
        ));
        $request->protocol = $req->server['server_protocol'] ?? 'HTTP/1.0';
        $request->get = $req->get ?? [];
        $request->post = $req->post ?? [];
        $request->cookie = $req->cookie ?? [];
        $request->files = $req->files ?? [];
        $request->header = $req->header ?? [];
        $request->server = $req->server ?? [];
        $request->server['swoole'] = true;
        if (array_key_exists('_method', $request->get)) {
            $request->method = strtoupper($request->get['_method'] ?: $request->method);
            unset($request->get['_method']);
        }
        if (array_key_exists('_method', $request->post)) {
            $request->method = strtoupper($request->post['_method'] ?: $request->method);
            unset($request->post['_method']);
        }
        if (in_array($request->method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
            return $request;
        }
        if (!preg_match('/^[A-Z]++$/D', $request->method)) {
            throw new RuntimeException(sprintf('Invalid method override "%s".', $request->method));
        }
        $request->body = $req->rawcontent();
        return $request;
    }

    public static function fromGlobal(): Request
    {
        $request = new self();
        $request->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $request->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $request->url = new URL(sprintf(
            '%s://%s:%s:%d%s',
            to_bool($_SERVER['HTTPS'] ?? 'off') ? 'https' : 'http',
            array_key_exists('REMOTE_USER', $_SERVER) ? $_SERVER['REMOTE_USER'] . '@' : '',
            $_SERVER['HTTP_HOST'] ?? 'localhost',
            $_SERVER['SERVER_PORT'] ?? 80,
            $_SERVER['REQUEST_URI'] ?? '/'
        ));
        $request->protocol = $_SERVER['HTTP_SERVER_PROTOCOL'] ?? 'HTTP/1.0';
        $request->get = $_GET ?? [];
        $request->post = $_POST ?? [];
        $request->cookie = $_COOKIE ?? [];
        self::migrateFiles($_FILES ?? [], $request->files);
        foreach ($_SERVER as $key => $value) {
            if (mb_strpos($key, 'HTTP_') !== 0) {
                if ($key === 'HTTP_AUTHORIZATION') {
                    list ($user, $pw) = explode(':', base64_decode(mb_substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
                    $request->header['php_auth_user'] = $user ?? null;
                    $request->header['php_auth_pass'] = $pw ?? null;
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
        if (array_key_exists('_method', $request->get)) {
            $request->method = strtoupper($request->get['_method'] ?: $request->method);
            unset($request->get['_method']);
        }
        if (array_key_exists('_method', $request->post)) {
            $request->method = strtoupper($request->post['_method'] ?: $request->method);
            unset($request->post['_method']);
        }
        if (in_array($request->method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
            return $request;
        }
        if (!preg_match('/^[A-Z]++$/D', $request->method)) {
            throw new RuntimeException(sprintf('Invalid method override "%s".', $request->method));
        }
        $request->body = (string)file_get_contents("php://input");
        return $request;
    }

    public function __construct()
    {
        $this->id = guid();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function get(string|array|null $key = null, mixed $value = null): mixed
    {
        if (is_null($key)) {
            return $this->get;
        }
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->get[$k] = $value;
            }
            return $this;
        }
        if (is_null($value)) {
            return $this->get[$key] ?? null;
        }
        $this->get[$key] = $value;
        return $this;
    }

    public function post(string|array|null $key = null, mixed $value = null): mixed
    {
        if (is_null($key)) {
            return $this->post;
        }
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->post[$k] = $value;
            }
            return $this;
        }
        if (is_null($value)) {
            return $this->post[$key] ?? null;
        }
        $this->post[$key] = $value;
        return $this;
    }

    public function cookie(string|array|null $key = null, mixed $value = null): mixed
    {
        if (is_null($key)) {
            return $this->cookie;
        }
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->cookie[$k] = $value;
            }
            return $this;
        }
        if (is_null($value)) {
            return $this->cookie[$key] ?? null;
        }
        $this->cookie[$key] = $value;
        return $this;
    }

    public function file(string|array|null $key = null, mixed $value = null): mixed
    {
        if (is_null($key)) {
            return $this->files;
        }
        if (is_array($key)) {
            foreach ($key as $k => $value) {
                $this->files[$k] = $value;
            }
            return $this;
        }
        if (is_null($value)) {
            return $this->files[$key] ?? null;
        }
        $this->files[$key] = $value;
        return $this;
    }

    public function route(Route $route = null): static|Route|null
    {
        if (is_null($route)) {
            return $this->route;
        }
        $this->route = $route;
        return $this;
    }

    public function session(SessionInterface $session = null): static|SessionInterface|null
    {
        if (is_null($session)) {
            return $this->session;
        }
        $this->session = $session;
        return $this;
    }

    public function user(mixed $user = null): mixed
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
        $request->id = $this->id;
        $request->url = $this->url;
        $request->get = $this->get;
        $request->post = $this->post;
        $request->header = $this->header;
        $request->server = $this->server;
        $request->cookie = $this->cookie;
        $request->files = $this->files;
        $request->route = $this->route;
        $request->session = $this->session;
        $request->user = $this->user;
        return $request;
    }

    public function method(string $method = null): Request|string
    {
        if (is_null($method)) {
            return $this->method;
        }
        $request = $this->clone();
        $request->method = $method;
        return $request;
    }

    public function url(URL $url = null, bool $preserveHost = false): URL|Request
    {
        if (is_null($url)) {
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
        if (is_null($protocol)) {
            return $this->protocol;
        }
        $request = $this->clone();
        $request->protocol = $protocol;
        return $request;
    }

    public function header(string $key = null, mixed $value = null): Request|array|string
    {
        if (is_null($key)) {
            return $this->header;
        }
        $key = mb_strtolower(str_replace('-', '_', $key));
        if (is_null($value)) {
            return $this->header[$key] ?? [];
        }
        $request = $this->clone();
        $request->header[$key] = [$value];
        return $request;
    }

    public function server(string $key = null, mixed $value = null): Request|array|string
    {
        if (is_null($key)) {
            return $this->server;
        }
        $key = mb_strtolower(str_replace('-', '_', $key));
        if (is_null($value)) {
            return $this->server[$key] ?? [];
        }
        $request = $this->clone();
        $request->server[$key] = [$value];
        return $request;
    }

    public function body(string $body = null): Request|string|null
    {
        if (is_null($body)) {
            return $this->body;
        }
        $request = $this->clone();
        $request->body = $body;
        return $request;
    }

    public function ip(string $ip = null): static|string|null
    {
        if (is_null($ip)) {
            return $this->ip;
        }
        $this->ip = $ip;
        return $this;
    }

    private static function migrateFiles(array $files, array &$results): void
    {
        foreach ($files as $key => $file) {
            if (!is_array($file)) {
                continue;
            }

            if (
                array_key_exists('name', $file)
                && array_key_exists('type', $file)
                && array_key_exists('size', $file)
                && array_key_exists('tmp_name', $file)
                && array_key_exists('error', $file)
            ) {
                $results[$key] = new UploadedFile($file);
            } else {
                $results[$key] = [];
                self::migrateFiles($file, $results[$key]);
            }
        }
    }

}
