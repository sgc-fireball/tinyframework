<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use PHPUnit\Framework\TestCase;
use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\Tests\Feature\FeatureTestCase;

abstract class FileSystemTestCase extends FeatureTestCase
{
    protected FileSystemInterface|null $fileSystem = null;

    public function testFileExists(): void
    {
        $file = 'testFile';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($file));
        $this->assertFalse($this->fileSystem->fileExists($file));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertTrue($this->fileSystem->fileExists($file));
    }

    public function testDirectoryExists(): void
    {
        $folder = 'testDirectory';
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($folder));
        $this->assertFalse($this->fileSystem->directoryExists($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->createDirectory($folder));
        $this->assertTrue($this->fileSystem->directoryExists($folder));
    }

    public function testExists(): void
    {
        $path = 'testExists';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($path . '1'));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($path . '2'));
        $this->assertFalse($this->fileSystem->exists($path . '1'));
        $this->assertFalse($this->fileSystem->exists($path . '2'));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($path . '1', $rand));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->createDirectory($path . '2'));
        $this->assertTrue($this->fileSystem->exists($path . '1'));
        $this->assertTrue($this->fileSystem->exists($path . '2'));
    }

    public function testWrite(): void
    {
        $file = 'testWrite';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertEquals($rand, $this->fileSystem->read($file));
    }

    public function testWriteStream(): void
    {
        $rand = mt_rand(0, 1_000_000);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, (string)$rand);
        fseek($fp, 0);
        $file = 'testWriteStream';
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->writeStream($file, $fp));
        $this->assertEquals($rand, $this->fileSystem->read($file));
    }

    public function testRead(): void
    {
        $file = 'testRead';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertEquals($rand, $this->fileSystem->read($file));
    }

    public function testReadStream(): void
    {
        $rand = mt_rand(0, 1_000_000);
        $file = 'testReadStream';
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $result = fread($this->fileSystem->readStream($file), 4096);
        $this->assertEquals($rand, $result);
    }

    public function testDelete(): void
    {
        $rand = mt_rand(0, 1_000_000);
        $file = 'testDelete';
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertTrue($this->fileSystem->fileExists($file));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($file));
        $this->assertFalse($this->fileSystem->fileExists($file));
    }

    public function testCreateDirectory(): void
    {
        $folder = 'testDirectory';
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->createDirectory($folder));
        $this->assertTrue($this->fileSystem->directoryExists($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($folder));
        $this->assertFalse($this->fileSystem->directoryExists($folder));
    }

    public function testList(): void
    {
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write('testList', $rand));
        $list = $this->fileSystem->list('/');
        $this->assertGreaterThanOrEqual(1, count($list));
    }

    public function testListV2(): void
    {
        $folder = 'testListV2';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->createDirectory($folder));
        for ($i = 0; $i < 10; $i++) {
            $file = $folder . '/' . $i;
            $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        }
        $list = $this->fileSystem->list($folder);
        $this->assertEquals(10, count($list));
        $this->fileSystem->delete($folder);
    }

    public function testMove(): void
    {
        $file = 'testMove';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file . '1', $rand));
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->move($file . '1', $file . '2'));
        $this->assertFalse($this->fileSystem->fileExists($file . '1'));
        $this->assertEquals($rand, $this->fileSystem->read($file . '2'));
    }

    public function testCopy(): void
    {
        $file = 'testCopy';
        $rand = mt_rand(0, 1_000_000);

        # cleanup
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->delete($file . '2'));
        $this->assertFalse($this->fileSystem->fileExists($file . '2'));

        # prepare
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file . '1', $rand));
        $this->assertTrue($this->fileSystem->fileExists($file . '1'));

        # test
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->copy($file . '1', $file . '2'));
        $this->assertTrue($this->fileSystem->fileExists($file . '1'));
        $this->assertEquals($rand, $this->fileSystem->read($file . '2'));
    }

    public function testFileSize(): void
    {
        $file = 'testFileSize';
        $rand = (string)mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertEquals(strlen($rand), $this->fileSystem->fileSize($file));
    }

    public function testMimeType(): void
    {
        $file = 'testMimeType';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $this->fileSystem->write($file, $rand));
        $this->assertEquals('text/plain', $this->fileSystem->mimeType($file));
    }

    abstract public function testUrl(): void;

    abstract public function testTemporaryUrl(): void;
}
