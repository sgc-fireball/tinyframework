<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use TinyFramework\Http\Response;
use TinyFramework\Http\URL;

class HTTP
{

    use Macroable;

    private array $fakes = [];

    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Missing curl. Please install php-curl first.');
        }
    }

    public function fake(array|null $fakes = []): self
    {
        if (is_array($fakes)) {
            foreach ($fakes as $url => $response) {
                assert($response instanceof Response);
                $this->fakes[$url] = $response;
            }
        } else {
            $this->fakes = [];
        }
        return $this;
    }

    public function get(URL|string $url, array $headers = []): Response
    {
        return $this->request('GET', $url, null, $headers);
    }

    public function head(URL|string $url, array $headers = []): Response
    {
        return $this->request('HEAD', $url, null, $headers);
    }

    public function option(URL|string $url, array $headers = []): Response
    {
        return $this->request('OPTION', $url, null, $headers);
    }

    public function post(URL|string $url, mixed $body, array $headers = []): Response
    {
        return $this->request('POST', $url, $body, $headers);
    }

    public function put(URL|string $url, mixed $body, array $headers = []): Response
    {
        return $this->request('PUT', $url, $body, $headers);
    }

    public function patch(URL|string $url, mixed $body, array $headers = []): Response
    {
        return $this->request('PATCH', $url, $body, $headers);
    }

    public function delete(URL|string $url, mixed $body, array $headers = []): Response
    {
        return $this->request('DELETE', $url, $body, $headers);
    }

    public function request(string $method, URL|string $url, mixed $body = null, array $headers = []): Response
    {
        $_url = is_string($url) ? $url : $url->__toString();
        if (isset($this->fakes[$_url])) {
            return $this->fakes[$_url];
        }
        $ch = curl_init($_url);
        curl_setopt($ch, \CURLOPT_CUSTOMREQUEST, $method);
        if (!in_array($method, ['GET', 'OPTION', 'HEAD'])) {
            curl_setopt($ch, \CURLOPT_POSTFIELDS, $body);
        }
        if ($method === 'HEAD') {
            curl_setopt($ch, \CURLOPT_NOBODY, true);
        }

        $headers = array_map(function (string $value, string $key) {
            return $key . ': ' . $value;
        }, array_values($headers), array_keys($headers));
        curl_setopt($ch, \CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, \CURLOPT_HEADER, true);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if (!$result) {
            throw new \RuntimeException(sprintf('ERROR[%d] %s', curl_errno($ch), curl_error($ch)));
        }

        $headerSize = curl_getinfo($ch, \CURLINFO_HEADER_SIZE);
        $response = Response::new(
            substr($result, $headerSize),
            curl_getinfo($ch, \CURLINFO_HTTP_CODE)
        );

        $header = trim(substr($result, 0, $headerSize));
        $header = explode("\n", $header);
        $header = array_map('trim', $header);
        array_shift($header);
        array_walk(
            $header,
            function ($line) use ($response) {
                [$key, $value] = explode(': ', $line, 2);
                $response->header($key, $value);
            }
        );

        return $response;
    }

}
