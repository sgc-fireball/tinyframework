<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\LocalFileSystem;
use TinyFramework\Http\Request;
use TinyFramework\Http\URL;

class LocalFileSystemTest extends FileSystemTestCase
{

    public function getFileSystems(): array
    {
        parent::setUp();
        $basePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit';
        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }
        return [
            [
                new LocalFileSystem([
                    'name' => 'test',
                    'allowUnsafe' => true,
                    'basePath' => $basePath,
                ]),
                env('APP_URL'),
            ],
        ];
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testValidUrl';
        $fileSystem = new LocalFileSystem(['name' => 'test', 'basePath' => public_dir()]);
        $this->container->singleton('filesystem.test', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->url($file);
        $this->assertEquals($publicUrl . '/' . $file, $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testValidTemporaryUrl';
        $fileSystem = new LocalFileSystem(['name' => 'test', 'basePath' => public_dir()]);
        $this->container->singleton('filesystem.test', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->temporaryUrl($file, 30);
        $this->assertEquals($publicUrl . '/' . $file, $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testPrivateUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testValidUrl';
        $fileSystem = new LocalFileSystem(['basePath' => sys_get_temp_dir(), 'allowUnsafe' => true, 'name' => 'tmp']);
        $this->container->singleton('filesystem.tmp', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->url($file);
        $this->assertStringStartsWith($publicUrl . '/__download/tmp/' . $file . '?sign=ey', $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testPrivateTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testValidTemporaryUrl';
        $fileSystem = new LocalFileSystem(['basePath' => sys_get_temp_dir(), 'allowUnsafe' => true, 'name' => 'tmp']);
        $this->container->singleton('filesystem.tmp', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->temporaryUrl($file, 30);
        $this->assertStringStartsWith($publicUrl . '/__download/tmp/' . $file . '?sign=ey', $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testInvalidUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(\RuntimeException::class);
        $url = $fileSystem->url('file');
        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertNotEquals(200, $response->code());
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testInvalidTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $this->expectException(\RuntimeException::class);
        $url = $fileSystem->temporaryUrl('file', 30);
        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertNotEquals(200, $response->code());
    }
}
