<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\FileSystem\LocalFileSystem;
use TinyFramework\Helpers\HTTP;
use TinyFramework\Http\Request;
use TinyFramework\Http\URL;

class LocalFileSystemTest extends FileSystemTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $rootFs = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'phpunit';
        if (!is_dir($rootFs)) {
            mkdir($rootFs, 0755, true);
        }
        $this->fileSystem = new LocalFileSystem([
            'name' => 'test',
            'allowUnsafe' => true,
            'basePath' => $rootFs,
        ]);
        $this->container->singleton('filesystem.test', $this->fileSystem);
    }

    public function testUrl(): void
    {
        $file = 'testValidUrl';
        $fileSystem = new LocalFileSystem(['name' => 'test', 'basePath' => public_dir()]);
        $this->container->singleton('filesystem.test', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->url($file);
        $this->assertEquals(env('APP_URL') . '/' . $file, $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    public function testTemporaryUrl(): void
    {
        $file = 'testValidTemporaryUrl';
        $fileSystem = new LocalFileSystem(['name' => 'test', 'basePath' => public_dir()]);
        $this->container->singleton('filesystem.test', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->temporaryUrl($file, 30);
        $this->assertEquals(env('APP_URL') . '/' . $file, $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    public function testPrivateUrl(): void
    {
        $file = 'testValidUrl';
        $fileSystem = new LocalFileSystem(['basePath' => sys_get_temp_dir(), 'allowUnsafe' => true, 'name' => 'tmp']);
        $this->container->singleton('filesystem.tmp', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->url($file);
        $this->assertStringStartsWith(env('APP_URL') . '/__download/tmp/' . $file . '?sign=ey', $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    public function testPrivateTemporaryUrl(): void
    {
        $file = 'testValidTemporaryUrl';
        $fileSystem = new LocalFileSystem(['basePath' => sys_get_temp_dir(), 'allowUnsafe' => true, 'name' => 'tmp']);
        $this->container->singleton('filesystem.tmp', $fileSystem);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, ''));
        $url = $fileSystem->temporaryUrl($file, 30);
        $this->assertStringStartsWith(env('APP_URL') . '/__download/tmp/' . $file . '?sign=ey', $url);

        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertEquals(200, $response->code());
        $fileSystem->delete($file);
    }

    public function testInvalidUrl(): void
    {
        $this->expectException(\RuntimeException::class);
        $url = $this->fileSystem->url('file');
        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertNotEquals(200, $response->code());
    }

    public function testInvalidTemporaryUrl(): void
    {
        $this->expectException(\RuntimeException::class);
        $url = $this->fileSystem->temporaryUrl('file', 30);
        $request = Request::factory('GET', new URL($url));
        $response = $this->request($request);
        $this->assertNotEquals(200, $response->code());
    }
}
