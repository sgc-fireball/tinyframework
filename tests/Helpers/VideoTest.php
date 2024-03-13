<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Image;
use TinyFramework\Helpers\Video;

class VideoTest extends TestCase
{
    public function testMp4Duration(): void
    {
        if (!command_exists('ffmpeg')) {
            $this->markTestSkipped('Missing ffmpeg.');
        }
        if (!command_exists('ffprobe')) {
            $this->markTestSkipped('Missing ffprobe.');
        }
        $path = 'tests/assets/video.mp4';
        $this->assertTrue(is_file($path));
        $this->assertTrue(is_readable($path));
        $this->assertEquals(15, Video::createFromFile($path)->duration());
    }

    public function testMp4Attributes(): void
    {
        if (!command_exists('ffmpeg')) {
            $this->markTestSkipped('Missing ffmpeg.');
        }
        if (!command_exists('ffprobe')) {
            $this->markTestSkipped('Missing ffprobe.');
        }
        $path = 'tests/assets/video.mp4';
        $this->assertTrue(is_file($path));
        $this->assertTrue(is_readable($path));

        $video = Video::createFromFile($path);
        $this->assertEquals(1280, $video->width());
        $this->assertEquals(720, $video->height());
        $this->assertEquals(24, $video->fps());
    }

    public function testMp4Thumbnail(): void
    {
        if (!command_exists('ffmpeg')) {
            $this->markTestSkipped('Missing ffmpeg.');
        }
        if (!command_exists('ffprobe')) {
            $this->markTestSkipped('Missing ffprobe.');
        }
        $path = 'tests/assets/video.mp4';
        $this->assertTrue(is_file($path));
        $this->assertTrue(is_readable($path));

        $image = Video::createFromFile($path)->frame();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertTrue(is_file($image->getPath()));
        $this->assertTrue(is_readable($image->getPath()));
        $this->assertEquals(1280, $image->getWidth());
        $this->assertEquals(720, $image->getHeight());
    }
}
