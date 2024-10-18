<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use Imagick;
use PHPUnit\Framework\TestCase;
use TinyFramework\Helpers\Image;
use TinyFramework\Helpers\ImageOptimizer;
use TinyFramework\Helpers\Pdf;

class PdfTest extends TestCase
{

    public function testConstruct(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('Missing ext-imagick.');
        }
        if (!command_exists('convert')) {
            $this->markTestSkipped('Missing imagick cli programms.');
        }

        $path = getcwd() . '/tests/assets/test.pdf';
        $this->assertFileExists($path);
        $pdf = new Pdf($path);
        $this->assertInstanceOf(Pdf::class, $pdf);
        $this->assertEquals(1, $pdf->getPages());
        $this->assertEquals(595, $pdf->getWidth());
        $this->assertEquals(842, $pdf->getHeight());
    }

    public function testPdfPreview(): void
    {
        if (!extension_loaded('imagick')) {
            $this->markTestSkipped('Missing ext-imagick.');
        }
        if (!command_exists('convert')) {
            $this->markTestSkipped('Missing imagick cli programms.');
        }

        $path = getcwd() . '/tests/assets/test.pdf';
        $this->assertFileExists($path);
        $pdf = new Pdf($path);
        $this->assertInstanceOf(Pdf::class, $pdf);
        for ($page = 1; $page <= $pdf->getPages(); $page++) {
            // get thumbnail of page N
            $thumbnail = $pdf->thumbnail($page);
            $this->assertInstanceOf(Image::class, $thumbnail);

            // store preview as png
            $thumbnailPath = getcwd() . '/tests/assets/pdf-preview-' . $page . '.png';
            $thumbnail->savePng($thumbnailPath);
            $this->assertFileExists($thumbnailPath);

            // optimize / compress image
            ImageOptimizer::optimizePng($thumbnailPath); // it is highly recommended, to run this function every time!
            $this->assertFileExists($thumbnailPath);
        }
    }

}
