<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use TinyFramework\Session\SessionInterface;

class Response
{
    public static array $multiLineHeader = [
        'set-cookie',
        'server-timing',
    ];

    public static array $codes = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        419 => 'Page Expired',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Maintenance Mode',
    ];

    private int $code = 200;

    private string $protocol = 'HTTP/1.0';

    private array $headers = [
        'content-type' => 'text/html; charset=utf-8',
        #'content-security-policy' => "default-src 'none'; script-src 'self' 'unsafe-inline'; connect-src 'self'; img-src 'self'; style-src 'self' 'unsafe-inline'; font-src 'self';",
        #'permissions-policy' => 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()',
        #'referrer-policy' => 'strict-origin-when-cross-origin',
        #'x-content-type-options' => 'nosniff',
        #'x-frame-options' => 'SAMEORIGIN',
        #'x-xss-protection' => '1; mode=block',
    ];

    private ?string $content = null;

    private string $id;

    private ?SessionInterface $session = null;

    public static function new(?string $content = '', int $code = 200, array $headers = []): Response
    {
        return (new Response())->code($code)->content($content ?? '')->headers($headers);
    }

    public static function view(string $file, array $data = [], int $code = 200, array $headers = []): Response
    {
        return self::new(container('view')->render($file, $data), $code, $headers);
    }

    public static function json(array $json = [], int $code = 200): Response
    {
        return self::new(json_encode($json), $code)->type('application/json; charset=utf-8');
    }

    public static function error(int $code = 400, array $headers = []): Response
    {
        $content = 'HTTP Status ' . $code;
        if (\array_key_exists($code, self::$codes)) {
            $content = $code . ' ' . self::$codes[$code];
        }
        return self::new($content, $code, $headers);
    }

    public static function redirect(string $to, int $code = 302, array $headers = []): Response
    {
        $code = \in_array($code, [301, 302]) ? $code : 302;
        return self::new('', $code, $headers)->header('location', $to);
    }

    public static function back(string $fallback = null, array $headers = []): Response
    {
        $request = container('request');
        assert($request instanceof Request);
        $back = ($request->header('referer') ?: $fallback) ?: $request->url()->__toString();
        return self::redirect($back, 302, $headers);
    }

    public function __construct()
    {
        $this->id = guid();
    }

    public function session(SessionInterface $session = null): static|SessionInterface|null
    {
        if ($session === null) {
            return $this->session;
        }
        $this->session = $session;
        return $this;
    }

    public function with(string $key, mixed $value): static
    {
        if ($this->session) {
            $flash = $this->session->get('flash') ?? [];
            $flash[$key][] = $value;
            $this->session->set('flash', $flash);
        }
        return $this;
    }

    public function withInput(array $input = null): static
    {
        if ($this->session) {
            if ($input === null) {
                $request = container('request');
                assert($request instanceof Request);
                $input = array_merge($request->get(), $request->post());
            }
            $this->session->set('flash_inputs', $input);
        }
        return $this;
    }

    public function withErrors(array $errors = []): static
    {
        if ($this->session) {
            $this->session->set('flash_errors', $errors);
        }
        return $this;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(int $code = null): static|int
    {
        if ($code === null) {
            return $this->code;
        }
        $this->code = $code;
        return $this;
    }

    public function protocol(string $protocol = null): static|string
    {
        if ($protocol === null) {
            return $this->protocol;
        }
        $this->protocol = $protocol;
        return $this;
    }

    public function type(string $type = null): static|string
    {
        return $this->header('content-type', $type);
    }

    public function header(string $key = null, string $value = null): static|array|null|string
    {
        if ($key === null) {
            return $this->headers;
        }
        if ($value === null) {
            return $this->headers[$key] ?? null;
        }
        $key = mb_strtolower($key);
        if (in_array($key, self::$multiLineHeader)) {
            $values = $this->headers[$key] ?? [];
            $values[] = $value;
            $value = $values;
        }
        $this->headers[$key] = $value;
        return $this;
    }

    public function headers(array $headers = null): static|array
    {
        if (!is_array($headers)) {
            return $this->headers;
        }
        foreach ($headers as $key => $value) {
            $this->header($key, $value ?? '');
        }
        return $this;
    }

    public function content(string $content = null): static|string|null
    {
        if ($content === null) {
            return $this->content;
        }
        $this->content = $content;
        return $this;
    }

    public function send(): static
    {
        header(
            sprintf('%s %d %s', $this->protocol, $this->code, static::$codes[$this->code]),
            true,
            $this->code
        );
        foreach ($this->headers as $key => $value) {
            if (in_array($key, self::$multiLineHeader) && is_array($value)) {
                foreach ($value as $val) {
                    header(sprintf('%s: %s', $key, $val), $key === 'content-type');
                }
            } else {
                header(sprintf('%s: %s', $key, $value), $key === 'content-type');
            }
        }
        echo $this->content;
        flush();
        return $this;
    }

    public function __toString(): string
    {
        $response = sprintf("%s %d %s\n", $this->protocol, $this->code, static::$codes[$this->code]);
        foreach ($this->headers as $key => $value) {
            if (in_array($key, self::$multiLineHeader) && is_array($value)) {
                foreach ($value as $val) {
                    $response .= sprintf("%s: %s\n", $key, $val);
                }
            } else {
                $response .= sprintf("%s: %s\n", $key, $value);
            }
        }
        $response .= "\n" . $this->content;
        return $response;
    }
}
