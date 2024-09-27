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

    public function resolutionProvider(): array
    {
        return [
            [Video::RESOLUTION_240P],
            [Video::RESOLUTION_360P],
            [Video::RESOLUTION_480P],
            [Video::RESOLUTION_720P],
            [Video::RESOLUTION_1080P],
            [Video::RESOLUTION_1440P],
        ];
    }

    /**
     * @dataProvider resolutionProvider
     */
    public function testResolution(int $resolution): void
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

        $target = sprintf('tests/assets/video_%d.mp4', $resolution);
        if (file_exists($target)) {
            unlink($target);
        }

        $this->assertFalse(is_file($target));
        $this->assertInstanceOf(Video::class, Video::createFromFile($path)->resolution($resolution, $target));
        $this->assertTrue(is_file($target));
        $this->assertTrue(is_readable($target));
    }

    public function testWebVttThumbnails(): void
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
        [$thumbnail, $webvtt] = $video->webvtt('video.jpg');
        $this->assertInstanceOf(Image::class, $thumbnail);
        $this->assertIsString($webvtt);
        $this->assertStringStartsWith('WEBVTT', $webvtt);

        $vttThumbnail = str_replace('mp4', 'jpg', $path);
        $thumbnail->saveJpeg($vttThumbnail);

        $webVttPath = str_replace('mp4', 'vtt', $path);
        file_put_contents($webVttPath, $webvtt);

        $this->assertTrue(is_file($webVttPath));
        $this->assertTrue(is_readable($webVttPath));

        $this->assertTrue(is_file($vttThumbnail));
        $this->assertTrue(is_readable($vttThumbnail));
    }
}
