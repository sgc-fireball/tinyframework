<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\Exception\FileSystemException;
use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\FtpFileSystem;

class FtpFileSystemTest extends FileSystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $host = 'minio';
        if (gethostbyname($host) === $host) {
            $this->markTestSkipped('Please run phpunit inside docker-compose env. Could not found test minio host.');
        }
        if (!extension_loaded('ftp')) {
            $this->markTestSkipped('Missing ext-ftp.');
        }
    }

    public function getFileSystems(): array
    {
        parent::setUp();
        return [
            [
                new FtpFileSystem([
                    'ssl' => false,
                    'username' => 'tinyframework-minio',
                    'password' => 'tinyframework-minio',
                    'host' => 'minio',
                    'passiv' => true,
                    'basePath' => 'tinyframework-ftp',
                    'folderPermission' => null,
                    'filePermission' => null,
                ]),
                env('APP_URL'),
            ],
        ];
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testMove(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(FileSystemException::class);
        parent::testMove($fileSystem, $publicUrl);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testMimeType(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(FileSystemException::class);
        $fileSystem->mimeType('file');
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
        $fileSystem->temporaryUrl('file', 30);
    }

}
