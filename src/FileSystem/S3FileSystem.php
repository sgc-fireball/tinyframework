<?php

declare(strict_types=1);

namespace TinyFramework\FileSystem;

use TinyFramework\Exception\FileSystemException;
use TinyFramework\Helpers\HTTP;
use TinyFramework\Http\Response;
use TinyFramework\Http\URL;

/**
 * @see https://docs.aws.amazon.com/AmazonS3/latest/API/sigv4-auth-using-authorization-header.html
 */
class S3FileSystem extends FileSystemAwesome implements FileSystemInterface
{
    protected array $config = [
        'debug' => false,
        'access_key_id' => null,
        'secret_access_key' => null,
        'domain' => null,
        'public_domain' => null,
        'region' => 'eu-central-1',
        'ssl' => true,
        'bucket' => null,
        'host' => null,
        'use_path_style_endpoint' => false,
    ];

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
        $this->config['host'] ??= sprintf('%s.s3.%s.amazonaws.com', $this->config['bucket'], $this->config['region']);
    }

    public function fileExists(string $location): bool
    {
        $response = $this->s3HeadObject($location);
        return $response->code() !== 404;
    }

    public function directoryExists(string $location): bool
    {
        try {
            $list = $this->list(rtrim($location, '/'));
            return count($list) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function exists(string $location): bool
    {
        return $this->fileExists($location);
    }

    public function write(string $location, $contents, array $config = []): self
    {
        $headers = array_merge(
            ['Content-Type' => 'text/plain'],
            $config['headers'] ?? [],
            ['Content-Length' => strlen((string)$contents)]
        );
        if (array_key_exists('ttl', $config)) {
            $headers['X-Amz-Expires'] = (int)$config['ttl'];
        }
        $response = $this->s3PutObject($location, (string)$contents, $headers);
        if ($response->code() !== 200) {
            throw new FileSystemException('Could not write location.');
        }
        return $this;
    }

    public function writeStream(string $location, $contents, array $config = []): self
    {
        $content = '';
        while (!feof($contents)) {
            $content .= fread($contents, 4096);
        }
        return $this->write($location, $content);
    }

    public function read(string $location): string
    {
        $response = $this->s3GetObject($location);
        if ($response->code() !== 200) {
            throw new FileSystemException('Could not read location.');
        }
        return (string)$response->content();
    }

    public function readStream(string $location): mixed
    {
        $content = $this->read($location);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, $content);
        fseek($fp, 0);
        return $fp;
    }

    public function delete(string $location): self
    {
        $list = $this->list($location);
        foreach ($list as $subLocation) {
            $this->delete($location . '/' . $subLocation);
        }
        $response = $this->s3DeleteObject($location);
        if ($response->code() !== 204) {
            throw new FileSystemException('Could not delete location: ' . $location);
        }
        return $this;
    }

    public function createDirectory(string $location, array $config = []): self
    {
        return $this;
    }

    /**
     * @link https://docs.aws.amazon.com/AmazonS3/latest/API/API_ListObjectsV2.html
     */
    public function list(string $location, array $parameters = []): mixed
    {
        $list = [];
        $parameters = array_merge(
            ['max-keys' => 10000],
            $parameters,
        );
        $response = $this->s3ListObjectV2($location, $parameters);
        if ($response->code() !== 200) {
            throw new FileSystemException('Could not fetch list for: ' . $location);
        }
        $content = $response->content();
        if (!str_starts_with($content, '<?xml version="1.0" encoding="UTF-8"?>')) {
            return [];
        }
        $xml = simplexml_load_string($content);
        $json = json_decode(json_encode($xml), true);
        foreach (($json['Contents'] ?? []) as $item) {
            $list[] = $item['Key'];
        }
        if (
            array_key_exists('IsTruncated', $json) && $json['IsTruncated'] &&
            array_key_exists('NextContinuationToken', $json) && $json['NextContinuationToken']
        ) {
            $list = array_merge(
                $list,
                $this->list(
                    $location,
                    array_merge($parameters, ['continuation-token' => $json['NextContinuationToken']])
                )
            );
        }
        return $list;
    }

    public function move(string $source, string $destination, array $config = []): self
    {
        $this->copy($source, $destination)->delete($source);
        return $this;
    }

    public function copy(string $source, string $destination, array $config = []): self
    {
        $response = $this->s3CopyObject($source, $destination);
        if ($response->code() !== 200) {
            throw new FileSystemException('Could not copy source to destination.');
        }
        return $this;
    }

    public function fileSize(string $location): int
    {
        $response = $this->s3HeadObject($location);
        if ($response->code() !== 200) {
            throw new FileSystemException(
                'Argument #1 $location isn\'t a valid file path or couldn\'t detect filesize.'
            );
        }
        return (int)$response->header('content-length');
    }

    public function mimeType(string $location): string
    {
        $response = $this->s3HeadObject($location);
        [$mimeType,] = explode(';', $response->header('content-type'));
        return $mimeType ?? 'text/plain';
    }

    public function url(string $location): string
    {
        // @TODO with public_domain
        return $this->baseUrl($location)->__toString();
    }

    public function temporaryUrl(string $location, int $ttl = 604800, array $config = []): string
    {
        if ($ttl > 604800) {
            throw new FileSystemException('The maximum ttl is one week.');
        }

        $url = $this->baseUrl($location);
        if ($this->config['public_domain']) {
            $publicUrl = new URL($this->config['public_domain']);
            $url = $url->scheme($publicUrl->scheme())
                ->host($publicUrl->host())
                ->port($publicUrl->port());
        }
        return $this->signUrl('GET', $url, $ttl)->__toString();
    }

    private function s3HeadObject(string $path): Response
    {
        return $this->request('HEAD', $this->baseUrl($path));
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_GetObject.html
     */
    private function s3GetObject(
        string $path,
    ): Response {
        return $this->request('GET', $this->baseUrl($path));
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_PutObject.html
     */
    private function s3PutObject(
        string $path,
        string $content,
        array $headers = []
    ): Response {
        return $this->request('PUT', $this->baseUrl($path), $content, $headers);
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_ListObjectsV2.html
     */
    private function s3ListObjectV2(
        string $path,
        array $config = []
    ): Response {
        $config['max-keys'] ??= 10000;
        $config['delimiter'] = '/';
        $config['list-type'] = 2;
        $config['prefix'] = ltrim(trim($path, '/') . '/', '/');
        ksort($config);
        $url = $this->baseUrl('')->query($config);
        return $this->request('GET', $url);
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/API_DeleteObject.html
     */
    private function s3DeleteObject(
        string $path
    ): Response {
        return $this->request('DELETE', $this->baseUrl($path));
    }

    /**
     * @link https://docs.aws.amazon.com/AmazonS3/latest/API/API_CopyObject.html
     */
    private function s3CopyObject(
        string $source,
        string $destination
    ): Response {
        return $this->request('PUT', $this->baseUrl($destination), null, [
            'X-Amz-Copy-Source' => $this->baseUrl($source)->path(),
        ]);
    }

    private function baseUrl(string $path): URL
    {
        return new URL(
            sprintf(
                'http%s://%s/%s%s',
                $this->config['ssl'] ? 's' : '',
                $this->config['host'],
                $this->config['use_path_style_endpoint'] ? $this->config['bucket'] . '/' : '',
                ltrim($path, '/')
            )
        );
    }

    protected function createCredentialScope(string $date): string
    {
        $content = [];
        $content[] = $this->config['access_key_id'];
        $content[] = substr($date, 0, 8);
        $content[] = $this->config['region'];
        $content[] = 's3';
        $content[] = 'aws4_request';
        $result = implode('/', $content);
        return $result;
    }

    protected function getSignedHeaders(array $headers): array
    {
        $signedHeaders = [];
        foreach ($headers as $key => $value) {
            $_key = strtolower($key);
            if (!str_starts_with($_key, 'x-amz-') && strtolower($_key) !== 'host') {
                continue;
            }
            $signedHeaders[] = $key;
        }
        sort($signedHeaders);
        return $signedHeaders;
    }

    protected function request(string $method, URL $url, string $body = null, array $_headers = []): Response
    {
        $date = date('Ymd\THis\Z');
        $headers['Host'] = $this->config['host'];
        $headers['User-Agent'] = userAgent();

        $headers['X-Amz-Algorithm'] = 'AWS4-HMAC-SHA256';
        $headers['X-Amz-Content-Sha256'] = hash('sha256', (string)$body);
        $headers['X-Amz-Date'] = $date;
        $headers['X-Amz-Credential'] = $this->createCredentialScope($date);
        $headers = array_merge($headers, $_headers);
        $headers['Authorization'] = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s,SignedHeaders=%s,Signature=%s',
            $headers['X-Amz-Credential'],
            strtolower(implode(';', $this->getSignedHeaders($headers))),
            hash_hmac(
                'sha256',
                $this->createSign($method, $url, $date, $headers),
                $this->calculateSignatureKey($date)
            )
        );
        return (new HTTP())->request($method, $url, $body, $headers);
    }

    protected function signUrl(string $method, URL $url, $ttl = 604800): URL
    {
        $date = date('Ymd\THis\Z');
        $query = [
            'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential' => $this->createCredentialScope($date),
            'X-Amz-Date' => $date,
            'X-Amz-Expires' => $ttl,
            'X-Amz-SignedHeaders' => 'host',
        ];
        $host = $url->host();
        if ($url->port()) {
            $host .= ':' . $url->port();
        }
        $query['X-Amz-Signature'] = hash_hmac(
            'sha256',
            $this->createSign($method, $url->query($query), $date, ['Host' => $host]),
            $this->calculateSignatureKey($date)
        );
        return $url->query($query);
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/sig-v4-header-based-auth.html
     */
    private function createCanonicalRequestHash(string $method, URL $url, array &$headers): string
    {
        $content = [];
        $content[] = $method;
        $content[] = $url->path();
        $content[] = $url->query();
        $signedHeaders = $this->getSignedHeaders($headers);
        foreach ($signedHeaders as $key) {
            $value = $headers[$key];
            $content[] = sprintf('%s:%s', strtolower($key), $value);
        }
        $content[] = '';
        $content[] = strtolower(implode(';', $signedHeaders));
        $content[] = $headers['X-Amz-Content-Sha256'] ?? 'UNSIGNED-PAYLOAD';
        $_content = implode("\n", $content);
        $hash = hash('sha256', $_content);
        return $hash;
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/sig-v4-header-based-auth.html
     */
    private function createSign(string $method, URL $url, string $date, array $headers): string
    {
        $content = [];
        $content[] = 'AWS4-HMAC-SHA256';
        $content[] = $date;
        $content[] = sprintf(
            '%s/%s/%s/%s',
            substr($date, 0, 8),
            $this->config['region'],
            's3',
            'aws4_request'
        );
        $content[] = $this->createCanonicalRequestHash($method, $url, $headers);
        $_content = implode("\n", $content);
        return $_content;
    }

    /**
     * @see https://docs.aws.amazon.com/AmazonS3/latest/API/sig-v4-header-based-auth.html
     */
    private function calculateSignatureKey(
        string $date
    ): string {
        $dateKey = hash_hmac(
            'sha256',
            substr($date, 0, 8),
            "AWS4" . $this->config['secret_access_key'],
            true
        );
        $dateRegionKey = hash_hmac('sha256', $this->config['region'], $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);
        return $signingKey;
    }

}
