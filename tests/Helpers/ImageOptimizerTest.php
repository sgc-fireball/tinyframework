<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\ImageOptimizer;
use TinyFramework\Helpers\Str;

class ImageOptimizerTest extends TestCase
{
    public function testJpeg(): void
    {
        if (!command_exists('jpegoptim')) {
            $this->markTestSkipped('Missing jpegoptim.');
        }
        $path1 = 'tests/assets/image.jpg';
        $path2 = str_replace('image.', 'image.copy.', $path1);
        copy($path1, $path2);
        $this->assertTrue(file_exists($path2), 'File is missing: ' . $path2);
        $before = filesize($path2);
        $this->assertIsNumeric($before, 'Could not read filesize: ' . $path2);
        $this->assertTrue(ImageOptimizer::optimizeJpeg($path2), 'Optimization failed: ' . $path2);
        clearstatcache(true, $path2);
        $after = filesize($path2);
        $this->assertIsNumeric($after, 'Could not read filesize: ' . $path2);
        $this->assertLessThan($before, $after);
    }

    public function testPng(): void
    {
        if (!command_exists('optipng') && !command_exists('pngquant')) {
            $this->markTestSkipped('Missing optipng and pngquant.');
        }
        $path1 = 'tests/assets/image.png';
        $path2 = str_replace('image.', 'image.copy.', $path1);
        copy($path1, $path2);
        $this->assertTrue(file_exists($path2), 'File is missing: ' . $path2);
        $before = filesize($path2);
        $this->assertIsNumeric($before, 'Could not read filesize: ' . $path2);
        $this->assertTrue(ImageOptimizer::optimizePng($path2), 'Optimization failed: ' . $path2);
        clearstatcache(true, $path2);
        $after = filesize($path2);
        $this->assertIsNumeric($after, 'Could not read filesize: ' . $path2);
        $this->assertLessThan($before, $after);
    }

    public function testSvg(): void
    {
        if (!command_exists('svgo')) {
            $this->markTestSkipped('Missing svgo.');
        }
        $path1 = 'tests/assets/image.svg';
        $path2 = str_replace('image.', 'image.copy.', $path1);
        copy($path1, $path2);
        $this->assertTrue(file_exists($path2), 'File is missing: ' . $path2);
        $before = filesize($path2);
        $this->assertIsNumeric($before, 'Could not read filesize: ' . $path2);
        $this->assertTrue(ImageOptimizer::optimizeSvg($path2), 'Optimization failed: ' . $path2);
        clearstatcache(true, $path2);
        $after = filesize($path2);
        $this->assertIsNumeric($after, 'Could not read filesize: ' . $path2);
        $this->assertLessThan($before, $after);
    }

    public function testGif(): void
    {
        if (!command_exists('gifsicle')) {
            $this->markTestSkipped('Missing jpegoptim.');
        }
        $path1 = 'tests/assets/image.gif';
        $path2 = str_replace('image.', 'image.copy.', $path1);
        copy($path1, $path2);
        $this->assertTrue(file_exists($path2), 'File is missing: ' . $path2);
        $before = filesize($path2);
        $this->assertIsNumeric($before, 'Could not read filesize: ' . $path2);
        $this->assertTrue(ImageOptimizer::optimizeGif($path2), 'Optimization failed: ' . $path2);
        clearstatcache(true, $path2);
        $after = filesize($path2);
        $this->assertIsNumeric($after, 'Could not read filesize: ' . $path2);
        $this->assertLessThan($before, $after);
    }

    public function testWebp(): void
    {
        if (!command_exists('cwebp')) {
            $this->markTestSkipped('Missing cwebp.');
        }
        $path1 = 'tests/assets/image.webp';
        $path2 = str_replace('image.', 'image.copy.', $path1);
        copy($path1, $path2);
        $this->assertTrue(file_exists($path2), 'File is missing: ' . $path2);
        $before = filesize($path2);
        $this->assertIsNumeric($before, 'Could not read filesize: ' . $path2);
        $this->assertTrue(ImageOptimizer::optimizeWebP($path2), 'Optimization failed: ' . $path2);
        clearstatcache(true, $path2);
        $after = filesize($path2);
        $this->assertIsNumeric($after, 'Could not read filesize: ' . $path2);
        $this->assertLessThan($before, $after);
    }
}
