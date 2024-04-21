<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\S3FileSystem;
use TinyFramework\Helpers\HTTP;

class S3FileSystemTest extends FileSystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystem = new S3FileSystem([
            'access_key_id' => 'tinyframework-minio',
            'secret_access_key' => 'tinyframework-minio',
            'domain' => 'minio',
            'public_domain' => 'http://minio:9000',
            'region' => 'eu-central-1',
            'bucket' => 'tinyframework-public',

            'ssl' => false,
            'use_path_style_endpoint' => true,
            'host' => 'minio:9000',
        ]);
    }

    public function testCreateDirectory(): void
    {
        $this->markTestSkipped('Unsupported on S3, because directory are not supported.');
    }

    public function testDirectoryExists(): void
    {
        $this->markTestSkipped('Unsupported on S3, because directory are not supported.');
    }

    public function testExists(): void
    {
        $this->markTestSkipped('Unsupported on S3, because no directory exists.');
    }

    public function testUrl(): void
    {
        $file = 'testUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $url = $this->fileSystem->url($file);
        $this->assertEquals('http://minio:9000/tinyframework-public/testUrl', $url);
        $this->assertEquals(200, (new HTTP())->request('GET', $url)->code());
    }

    public function testTemporaryUrl(): void
    {
        $file = 'testTemporaryUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $url = $this->fileSystem->temporaryUrl($file, 60);
        $this->assertStringStartsWith('http://minio:9000/tinyframework-public/testTemporaryUrl?', $url);
        $this->assertStringContainsString('X-Amz-Expires=60', $url);
        $this->assertEquals(200, (new HTTP())->request('GET', $url)->code());
    }

    public function testInvalidTemporaryUrl(): void
    {
        $file = 'testTemporaryUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $url = $this->fileSystem->temporaryUrl($file, 0);
        $this->assertStringStartsWith('http://minio:9000/tinyframework-public/testTemporaryUrl?', $url);
        $this->assertStringContainsString('X-Amz-Expires=0', $url);
        sleep(1);
        $this->assertEquals(403, (new HTTP())->request('GET', $url)->code());
    }
}
