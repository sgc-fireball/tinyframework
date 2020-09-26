<?php declare(strict_types=1);

namespace TinyFramework\Http;

class Response
{

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
    ];

    private int $code = 200;

    private string $protocol = 'HTTP/1.0';

    private array $headers = [
        'content-type' => 'text/html; charset=utf-8'
    ];

    private ?string $content = null;

    private string $id;

    public static function error(int $code = 200): self
    {
        $content = 'HTTP Status ' . $code;
        if (array_key_exists($code, self::$codes)) {
            $content = $code . ' ' . self::$codes[$code];
        }
        return (new self())->content($content)->code($code);
    }

    public static function new(?string $content = '', int $code = 200): self
    {
        return (new self())->content($content ?? '')->code($code);
    }

    public static function redirect(string $to, int $code = 301): self
    {
        return (new self())->code($code)->header('location', $to);
    }

    public function __construct()
    {
        $this->id = guid();
    }

    public function id(): string
    {
        return $this->id;
    }

    public function code(int $code = null)
    {
        if (is_null($code)) {
            return $this->code;
        }
        $this->code = $code;
        return $this;
    }

    public function protocol(string $protocol = null)
    {
        if (is_null($protocol)) {
            return $this->protocol;
        }
        $this->protocol = $protocol;
        return $this;
    }

    public function type(string $type = null)
    {
        return $this->header('content-type', $type);
    }

    public function header(string $key = null, string $value = null)
    {
        if (is_null($key)) {
            return $this->headers;
        }
        if (is_null($value)) {
            return $this->headers[$key] ?? null;
        }
        $this->headers[strtolower($key)] = $value;
        return $this;
    }

    public function content(string $content = null)
    {
        if (is_null($content)) {
            return $this->content;
        }
        $this->content = $content;
        return $this;
    }

    public function send()
    {
        header(
            sprintf('%s %d %s', $this->protocol, $this->code, static::$codes[$this->code]),
            true,
            $this->code
        );
        foreach ($this->headers as $key => $value) {
            header($key . ': ' . $value, $key === 'content-type');
        }
        echo $this->content;
        flush();
        return $this;
    }

    public function __toString()
    {
        $response = sprintf("%s %d %s\n", $this->protocol, $this->code, static::$codes[$this->code]);
        foreach ($this->headers as $key => $value) {
            $response .= sprintf("%s: %s\n", $key, $value);
        }
        $response .= "\n" . $this->content;
        return $response;
    }

}
