<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\Exception\FileSystemException;
use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\SftpFileSystem;

class SftpFileSystemTest extends FileSystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $host = 'sftp';
        if (gethostbyname($host) === $host) {
            $this->markTestSkipped('Please run phpunit inside docker-compose env. Could not found test minio host.');
        }
        if (!extension_loaded('ssh2')) {
            $this->markTestSkipped('Missing ext-ssh2.');
        }
    }

    public function getFileSystems(): array
    {
        parent::setUp();
        $filesystems = [
            [
                new SftpFileSystem([
                    'host' => 'sftp',
                    'username' => 'tinyframework',
                    'password' => 'tinyframework',
                    'port' => 22,
                    'basePath' => 'tinyframework',
                    'filePermission' => 0644,
                    'folderPermission' => 0755,
                ]),
                'sftp://',
            ],
        ];
        if (file_exists(__DIR__.'/id_rsa.pub') && file_exists(__DIR__.'/id_rsa')) {
            $filesystems[] = [
                new SftpFileSystem([
                    'host' => 'sftp',
                    'username' => 'tinyframework',
                    'pubkeyfile' => __DIR__.'/id_rsa.pub',
                    'privkeyfile' => __DIR__.'/id_rsa',
                    'password' => null,
                    'port' => 22,
                    'basePath' => '/home/tinyframework',
                    'filePermission' => 0644,
                    'folderPermission' => 0755,
                ]),
                'sftp://',
            ];
        }
        return $filesystems;
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(FileSystemException::class);
        $fileSystem->url('file');
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(FileSystemException::class);
        $fileSystem->temporaryUrl('temporaryUrl', 30);
    }

}
