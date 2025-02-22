<?php

declare(strict_types=1);

namespace TinyFramework\FileSystem;

use TinyFramework\Exception\FileSystemException;
use TinyFramework\WebToken\JWT;

/**
 * @link https://github.com/thephpleague/flysystem/blob/3.x/src/Filesystem.php
 */
class LocalFileSystem extends FileSystemAwesome implements FileSystemInterface
{
    protected array $config = [
        'name' => '',
        'allowUnsafe' => false,
        'basePath' => '/',
        'folderPermission' => 0750,
        'filePermission' => 0640,
    ];

    protected JWT $jwt;

    public function __construct(array $config)
    {
        $this->config = array_merge($this->config, $config);
        $this->config['basePath'] = rtrim($this->config['basePath'], '/');

        $this->jwt = new JWT(JWT::ALG_HS512, secret());
    }

    private function getAbsolutePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(
            explode(DIRECTORY_SEPARATOR, $path),
            fn(string $value): bool => (bool)strlen($value)
        );
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            }
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    private function normalize(string $location): string
    {
        if (str_starts_with($location, $this->config['basePath'])) {
            $path = $location;
        } else {
            $path = $this->getAbsolutePath(
                sprintf(
                    '%s/%s',
                    $this->config['basePath'],
                    ltrim($location, '/')
                )
            );
        }
        if (!$this->config['allowUnsafe'] && !str_starts_with($path, $this->config['basePath'])) {
            throw new FileSystemException('Invalid file or directory path! Outside of context!');
        }
        return $path;
    }

    public function path(string $location): string
    {
        return $this->normalize($location);
    }

    public function fileExists(string $location): bool
    {
        return is_file($this->normalize($location));
    }

    public function directoryExists(string $location): bool
    {
        return is_dir($this->normalize($location));
    }

    public function exists(string $location): bool
    {
        return file_exists($this->normalize($location));
    }

    public function write(string $location, mixed $contents, array $config = []): self
    {
        $path = $this->normalize($location);
        if (file_put_contents($path, $contents) === false) {
            throw new FileSystemException('Could not write file.');
        }
        if (chmod($path, $this->config['filePermission']) === false) {
            throw new FileSystemException('Could not set file system permissions.');
        }
        return $this;
    }

    public function writeStream(string $location, mixed $contents, array $config = []): self
    {
        $this->assertIsResource($contents);
        $this->rewindStream($contents);
        $this->write($location, $contents, $config);
        return $this;
    }

    public function read(string $location): string
    {
        $path = $this->normalize($location);
        return file_get_contents($path);
    }

    public function readStream(string $location): mixed
    {
        $path = $this->normalize($location);
        $fp = fopen($path, 'rb');
        if (!$fp) {
            throw new FileSystemException('Could not open file stream.');
        }
        return $fp;
    }

    public function delete(string $location): self
    {
        $path = $this->normalize($location);
        if (is_file($path) || is_link($path)) {
            if (!unlink($path)) {
                throw new FileSystemException('Could not delete file.');
            }
        } elseif (is_dir($path)) {
            foreach ($this->list($path.'/') as $subPath) {
                $this->delete($subPath);
            }
            if (!rmdir($path)) {
                throw new FileSystemException('Could not delete file.');
            }
        }
        return $this;
    }

    public function createDirectory(string $location, array $config = []): self
    {
        $path = $this->normalize($location);
        if (!is_dir($path)) {
            mkdir($path, $this->config['folderPermission'], true);
        }
        return $this;
    }

    public function list(string $location): mixed
    {
        $path = rtrim($this->normalize($location),'/').'/*';
        return glob($path, GLOB_BRACE);
    }

    public function move(string $source, string $destination, array $config = []): self
    {
        $path = $this->normalize($destination);
        if (!rename($this->normalize($source), $path)) {
            throw new FileSystemException('Could not move file.');
        }
        if (chmod($path, $this->config['filePermission']) === false) {
            throw new FileSystemException('Could not set file system permissions.');
        }
        return $this;
    }

    public function copy(string $source, string $destination, array $config = []): self
    {
        $path = $this->normalize($destination);
        if (!copy($this->normalize($source), $path)) {
            throw new FileSystemException('Could not copy file.');
        }
        if (chmod($path, $this->config['filePermission']) === false) {
            throw new FileSystemException('Could not set file system permissions.');
        }
        return $this;
    }

    public function fileSize(string $location): int
    {
        $path = $this->normalize($location);
        if (is_file($path)) {
            return filesize($path);
        }
        throw new FileSystemException('Argument #1 $location isn\'t a valid file path or couldn\'t detect filesize.');
    }

    public function mimeType(string $location): string
    {
        $path = $this->normalize($location);
        if (!file_exists($path)) {
            throw new FileSystemException('Could not found file.');
        }
        $mimeType = mimetype_from_file($path);
        if (!$mimeType) {
            throw new FileSystemException('Could not detect mimetype.');
        }
        return $mimeType;
    }

    public function url(string $location): string
    {
        if (!$this->fileExists($location)) {
            throw new FileSystemException('Could not found file.');
        }
        $path = $this->normalize($location);
        if (str_starts_with($path, public_dir())) {
            return sprintf(
                '%s/%s',
                env('APP_URL'),
                str_replace(public_dir() . '/', '', $path)
            );
        }
        $sign = $this->jwt
            ->expirationTime(null)
            ->encode([
                'file' => $location,
                'disk' => $this->config['name'],
            ]);
        return sprintf(
            '%s/__download/%s/%s?sign=%s',
            env('APP_URL'),
            $this->config['name'],
            ltrim($location, '/'),
            $sign
        );
    }

    public function temporaryUrl(string $location, int $ttl, array $config = []): string
    {
        if (!$this->fileExists($location)) {
            throw new FileSystemException('Could not found file.');
        }
        $path = $this->normalize($location);
        if (str_starts_with($path, public_dir())) {
            return $this->url($location);
        }
        $sign = $this->jwt
            ->expirationTime(time() + $ttl)
            ->encode([
                'file' => $location,
                'disk' => $this->config['name'],
            ]);
        return sprintf(
            '%s/__download/%s/%s?sign=%s',
            env('APP_URL'),
            $this->config['name'],
            ltrim($location, '/'),
            $sign
        );
    }

    /**
     * @param resource $contents
     * @return void
     */
    private function assertIsResource($contents): void
    {
        if (is_resource($contents) === false) {
            throw new FileSystemException(
                "Invalid stream provided, expected stream resource, received " . gettype($contents)
            );
        } elseif ($type = get_resource_type($contents) !== 'stream') {
            throw new FileSystemException(
                "Invalid stream provided, expected stream resource, received resource of type " . $type
            );
        }
    }

    /**
     * @param resource $resource
     * @return void
     */
    private function rewindStream($resource): void
    {
        if (ftell($resource) !== 0 && stream_get_meta_data($resource)['seekable']) {
            rewind($resource);
        }
    }
}
