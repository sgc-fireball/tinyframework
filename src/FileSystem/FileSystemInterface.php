<?php

declare(strict_types=1);

namespace TinyFramework\FileSystem;

/**
 * @link https://github.com/thephpleague/flysystem/blob/3.x/src/Filesystem.php
 */
interface FileSystemInterface
{
    public function fileExists(string $location): bool;

    public function directoryExists(string $location): bool;

    public function exists(string $location): bool;

    public function write(string $location, mixed $contents, array $config = []): FileSystemInterface;

    /**
     * @param string $location
     * @param resource $contents
     * @param array $config
     * @return FileSystemInterface
     */
    public function writeStream(string $location, mixed $contents, array $config = []): FileSystemInterface;

    public function read(string $location): string;

    public function readStream(string $location): mixed;

    public function delete(string $location): FileSystemInterface;

    public function createDirectory(string $location, array $config = []): FileSystemInterface;

    public function list(string $location): mixed;

    public function move(string $source, string $destination, array $config = []): FileSystemInterface;

    public function copy(string $source, string $destination, array $config = []): FileSystemInterface;

    public function fileSize(string $location): int;

    public function mimeType(string $location): string;

    public function url(string $location): string;

    public function temporaryUrl(string $location, int $ttl, array $config = []): string;
}
