<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use Imagick;
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

    public function thumbnail(int $page = 1): ?Image
    {
        if ($page > $this->getPages()) {
            return null;
        }
        if ($page < 1) {
            throw new \InvalidArgumentException('Invalid page. Range: 1-' . $this->getPages());
        }
        $imagick = new Imagick($this->path . '[' . ($page - 1) . ']');
        $imagick->setImageFormat('png');

        $white = new Imagick();

        $white->newImage($imagick->getImageWidth(), $imagick->getImageHeight(), "white");
        $white->compositeimage($imagick, Imagick::COMPOSITE_OVER, 0, 0);
        $white->setImageFormat('png');
        #$white->writeImage('opaque.jpg');

        $blob = $white->getImageBlob();
        $imagick->destroy();
        $white->destroy();
        return Image::createFromString($blob);
    }

}
