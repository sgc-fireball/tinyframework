<?php

declare(strict_types=1);

namespace TinyFramework\Tests\FileSystem;

use TinyFramework\FileSystem\FileSystemInterface;
use TinyFramework\Tests\Feature\FeatureTestCase;

abstract class FileSystemTestCase extends FeatureTestCase
{
    abstract public function getFileSystems(): array;

    /**
     * @dataProvider getFileSystems
     */
    public function testFileExists(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testFile';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($file));
        $this->assertFalse($fileSystem->fileExists($file));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertTrue($fileSystem->fileExists($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testDirectoryExists(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $folder = 'testDirectory';
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($folder));
        $this->assertFalse($fileSystem->directoryExists($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->createDirectory($folder));
        $this->assertTrue($fileSystem->directoryExists($folder));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testExists(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $path = 'testExists';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($path . '1'));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($path . '2'));
        $this->assertFalse($fileSystem->exists($path . '1'));
        $this->assertFalse($fileSystem->exists($path . '2'));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($path . '1', $rand));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->createDirectory($path . '2'));
        $this->assertTrue($fileSystem->exists($path . '1'));
        $this->assertTrue($fileSystem->exists($path . '2'));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testWrite(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testWrite';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertEquals($rand, $fileSystem->read($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testWriteStream(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $rand = mt_rand(0, 1_000_000);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, (string)$rand);
        fseek($fp, 0);
        $file = 'testWriteStream';
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->writeStream($file, $fp));
        $this->assertEquals($rand, $fileSystem->read($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testRead(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testRead';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertEquals($rand, $fileSystem->read($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testReadStream(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $rand = mt_rand(0, 1_000_000);
        $file = 'testReadStream';
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $result = fread($fileSystem->readStream($file), 4096);
        $this->assertEquals($rand, $result);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testDelete(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $rand = mt_rand(0, 1_000_000);
        $file = 'testDelete';
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertTrue($fileSystem->fileExists($file));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($file));
        $this->assertFalse($fileSystem->fileExists($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testCreateDirectory(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $folder = 'testDirectory';
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->createDirectory($folder));
        $this->assertTrue($fileSystem->directoryExists($folder));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($folder));
        $this->assertFalse($fileSystem->directoryExists($folder));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testList(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write('testList', $rand));
        $list = $fileSystem->list('/');
        $this->assertGreaterThanOrEqual(1, count($list));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testListV2(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $folder = 'testListV2';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->createDirectory($folder));
        for ($i = 0; $i < 10; $i++) {
            $file = $folder . '/' . $i;
            $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        }
        $list = $fileSystem->list($folder);
        $this->assertEquals(10, count($list));
        $fileSystem->delete($folder);
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testMove(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testMove';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file . '1', $rand));
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->move($file . '1', $file . '2'));
        $this->assertFalse($fileSystem->fileExists($file . '1'));
        $this->assertTrue($fileSystem->fileExists($file . '2'));
        $this->assertEquals($rand, $fileSystem->read($file . '2'));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testCopy(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testCopy';
        $rand = mt_rand(0, 1_000_000);

        # cleanup
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->delete($file . '2'));
        $this->assertFalse($fileSystem->fileExists($file . '2'));

        # prepare
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file . '1', $rand));
        $this->assertTrue($fileSystem->fileExists($file . '1'));

        # test
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->copy($file . '1', $file . '2'));
        $this->assertTrue($fileSystem->fileExists($file . '1'));
        $this->assertTrue($fileSystem->fileExists($file . '2'));
        $this->assertEquals($rand, $fileSystem->read($file . '2'));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testFileSize(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testFileSize';
        $rand = (string)mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertEquals(strlen($rand), $fileSystem->fileSize($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    public function testMimeType(FileSystemInterface $fileSystem, string $publicUrl): void
    {
        $file = 'testMimeType';
        $rand = mt_rand(0, 1_000_000);
        $this->assertInstanceOf(FileSystemInterface::class, $fileSystem->write($file, $rand));
        $this->assertEquals('text/plain', $fileSystem->mimeType($file));
    }

    /**
     * @dataProvider getFileSystems
     */
    abstract public function testUrl(FileSystemInterface $fileSystem, string $publicUrl): void;

    /**
     * @dataProvider getFileSystems
     */
        abstract public function testTemporaryUrl(FileSystemInterface $fileSystem, string $publicUrl): void;
}
