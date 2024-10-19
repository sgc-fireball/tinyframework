<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use Imagick;
use ImagickPixel;
use RuntimeException;

class Pdf
{

    use Macroable;

    private Imagick $imagick;

    private string $path;

    private int|null $pages = null;

    private int|null $width = null;

    private int|null $height = null;

    public function __construct(string $path)
    {
        if (!extension_loaded('imagick')) {
            throw new RuntimeException('Please install the image ext-imagick first.');
        }
        if (!command_exists('convert')) {
            $this->markTestSkipped('Missing imagick cli programms.');
        }
        if (!is_readable($path)) {
            throw new RuntimeException('Argument #1 $path must be a valid and readable filepath.');
        }
        $this->path = $path;
        $this->imagick = new Imagick($this->path);
    }

    private function readSize(): void
    {
        $geometry = $this->imagick->getImageGeometry();
        $this->width = $geometry['width'];
        $this->height = $geometry['height'];
    }

    public function getWidth(): int
    {
        if ($this->width === null) {
            $this->readSize();
        }
        return $this->width;
    }

    public function getHeight(): int
    {
        if ($this->height === null) {
            $this->readSize();
        }
        return $this->height;
    }

    public function getPages(): int
    {
        if ($this->pages === null) {
            $this->pages = $this->imagick->getNumberImages();
        }
        return $this->pages;
    }

    public function thumbnail(int $page = 1, int $width = 1920): ?Image
    {
        if ($page > $this->getPages()) {
            return null;
        }
        if ($page < 1) {
            throw new \InvalidArgumentException('Invalid page. Range: 1-' . $this->getPages());
        }

        $imagick = new Imagick();
        $imagick->setResolution(300, 300);
        $imagick->readImage($this->path . '[' . ($page - 1) . ']');
        $imagick->scaleImage($width, (int)($width / $this->getWidth() * $this->getHeight()));
        $imagick->setImageColorspace(255);
        $imagick->setImageFormat('png');
        $imagick->setCompressionQuality(100);
        $imagick->setImageBackgroundColor(new ImagickPixel('white'));
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $blob = $imagick->getImageBlob();
        $imagick->destroy();
        return Image::createFromString($blob);
    }

}
