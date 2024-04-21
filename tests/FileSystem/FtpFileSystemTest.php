<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use TinyFramework\FileSystem\FtpFileSystem;

class FtpFileSystemTest extends FileSystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $host = 'minio';
        if (gethostbyname($host) === $host) {
            $this->markTestSkipped('Please run phpunit inside docker-compose env.');
        }
        if (!extension_loaded('ftp')) {
            $this->markTestSkipped('Missing ext-ftp.');
        }
        $this->fileSystem = new FtpFileSystem([
            'ssl' => false,
            'username' => 'tinyframework-minio',
            'password' => 'tinyframework-minio',
            'host' => $host,
            'passiv' => true,
            'basePath' => 'tinyframework-ftp',
            'folderPermission' => null,
            'filePermission' => null,
        ]);
    }

    public function testMove(): void
    {
        $this->markTestSkipped('Unsupported function on minio-ftp.');
    }

    public function testMimeType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->fileSystem->mimeType('file');
    }

    public function testUrl(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->fileSystem->url('file');
    }

    public function testTemporaryUrl(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->fileSystem->temporaryUrl('file', 30);
    }

}
