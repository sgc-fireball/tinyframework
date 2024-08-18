<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\S3FileSystem;
use TinyFramework\Helpers\HTTP;

class S3FileSystemTest extends FileSystemTestCase
{

    public function getFileSystems(): array
    {
        return [
            [
                new S3FileSystem([
                    'access_key_id' => 'tinyframework-minio',
                    'secret_access_key' => 'tinyframework-minio',
                    'domain' => 'http://minio:9000',
                    'public_domain' => $p = 'http://minio:9000/tinyframework-public',
                    'region' => 'minio',
                    'bucket' => 'tinyframework-public',
                    'use_path_style_endpoint' => true,
                    'basePath' => '',
                    'acl' => S3FileSystem::ACL_PUBLIC_READ,
                ]),
                $p,
            ],
            [
                new S3FileSystem([
                    'access_key_id' => 'tinyframework-minio',
                    'secret_access_key' => 'tinyframework-minio',
                    'domain' => 'http://minio:9000',
                    'public_domain' => $p = 'http://minio:9000/tinyframework-public/test-rh',
                    'region' => 'minio',
                    'bucket' => 'tinyframework-public',
                    'use_path_style_endpoint' => true,
                    'basePath' => 'test-rh',
                    'acl' => S3FileSystem::ACL_PUBLIC_READ,
                ]),
                $p,
            ],
        ];
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testCreateDirectory(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->markTestSkipped('Unsupported on S3, because directory are not supported.');
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testDirectoryExists(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->markTestSkipped('Unsupported on S3, because directory are not supported.');
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testDeepDirectoryExists(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->markTestSkipped('Unsupported on S3, because directory are not supported.');
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testExistsWithDirectory(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->markTestSkipped('Unsupported on S3, because no directory exists.');
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $url = $fileSystem->url($file);
        $this->assertEquals($publicUrl . '/testUrl', $url);
        $this->assertEquals(200, (new HTTP())->request('GET', $url)->code());
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testTemporaryUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $url = $fileSystem->temporaryUrl($file, 60);
        $this->assertStringStartsWith($publicUrl . '/testTemporaryUrl?', $url);
        $this->assertStringContainsString('X-Amz-Expires=60', $url);
        $this->assertEquals(200, (new HTTP())->request('GET', $url)->code());
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testInvalidTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testTemporaryUrl';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $url = $fileSystem->temporaryUrl($file, 0);
        $this->assertStringStartsWith($publicUrl . '/testTemporaryUrl?', $url);
        $this->assertStringContainsString('X-Amz-Expires=0', $url);
        sleep(1);
        $this->assertEquals(403, (new HTTP())->request('GET', $url)->code());
    }
}
