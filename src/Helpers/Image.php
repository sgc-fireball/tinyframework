<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use GdImage;
use InvalidArgumentException;
use RuntimeException;

class Image
{

    use Macroable;

    public const MAX_SIZE = 1920 * 2;

    private string|null $path = null;

    private GdImage|false $image = false;

    private int $width = 1;

    private int $height = 1;

    /**
     * @throws RuntimeException
     */
    public function __construct(int $width = 1, int $height = 1)
    {
        if (!extension_loaded('gd')) {
            throw new RuntimeException('Please install the image ext-gd first.');
        }
        if ($width > static::MAX_SIZE || $height > static::MAX_SIZE) {
            throw new RuntimeException(sprintf('Image is to large! (max %dx%d)', static::MAX_SIZE, static::MAX_SIZE));
        }
        $this->width = max(1, $width);
        $this->height = max(1, $height);
        $this->image = imagecreatetruecolor($this->width, $this->height);
        $this->allowAlpha();
        $this->fillAlpha();

        $this->path = null;
    }

    public function getPath(): string|null
    {
        return $this->path;
    }

    public static function createFromImage(string $imagePath, string|null $type = null): self
    {
        if ($type === null) {
            if (function_exists('exif_imagetype')) {
                if ($typeNumber = exif_imagetype($imagePath)) {
                    switch ($typeNumber) {
                        case IMAGETYPE_GIF:
                            $type = 'gif';
                            break;
                        case IMAGETYPE_JPEG:
                            $type = 'jpeg';
                            break;
                        case IMAGETYPE_PNG:
                            $type = 'png';
                            break;
                        case IMAGETYPE_WBMP:
                            $type = 'wbmp';
                            break;
                        case IMAGETYPE_XBM:
                            $type = 'xbm';
                            break;
                    }
                }
            }
            if ($type === null && function_exists('mime_content_type')) {
                $mimeType = mime_content_type($imagePath);
                if (str_starts_with($mimeType, 'image/')) {
                    $type = str_replace('image/', '', $mimeType);
                }
            }
            if ($type === null) {
                $info = pathinfo($imagePath);
                if (!isset($info['extension'])) {
                    throw new RuntimeException('Could not found file type by ' . $imagePath);
                }
                $type = $info['extension'];
            }
        }
        $type = ucfirst(strtolower($type));
        if (!method_exists(static::class, 'createFrom' . $type)) {
            throw new RuntimeException('Could not create Image from ' . $imagePath);
        }
        return call_user_func([static::class, 'createFrom' . $type], $imagePath);
    }

    /**
     * @throws RuntimeException
     */
    public static function createFromBmp(string $imagePath): self
    {
        if (!function_exists('imagecreatefrombmp')) {
            throw new RuntimeException('Function imagecreatefrombmp doesn\'t exists.');
        }

        $image = new Image();
        $image->image = imagecreatefrombmp($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromJpeg(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromjpeg($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    /**
     * @throws RuntimeException
     */
    public static function createFromWebp(string $imagePath): self
    {
        if (!function_exists('imagecreatefromwebp')) {
            throw new RuntimeException('Function imagecreatefromwebp doesn\'t exists.');
        }
        $image = new Image();
        $image->image = imagecreatefromwebp($imagePath);
        $image->allowAlpha();
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromString(string $imageString): self
    {
        $image = new Image();
        $image->image = imagecreatefromstring($imageString);
        $image->path = null;
        return $image->fixSizeInformation();
    }

    public static function createFromPng(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefrompng($imagePath);
        $image->allowAlpha();

        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromGif(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgif($imagePath);
        $image->allowAlpha();
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromWbmp(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromwbmp($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromXbm(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromxbm($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromXpm(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromxpm($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromGd(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgd($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    public static function createFromGd2(string $imagePath): self
    {
        $image = new Image();
        $image->image = imagecreatefromgd2($imagePath);
        $image->path = $imagePath;
        return $image->fixSizeInformation();
    }

    private function fixSizeInformation(): self
    {
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function rawExif(): array
    {
        if (!$this->path || !is_file($this->path) || !is_readable($this->path)) {
            return [];
        }
        $exif = @exif_read_data($this->path);
        if (!$exif) {
            return [];
        }
        return array_filter($exif, function (mixed $value): mixed {
            return $value === null;
        });
    }

    /**
     * @link https://github.com/getkirby/kirby/blob/main/src/Image/Exif.php
     */
    public function exif(): array
    {
        $exif = $this->rawExif();

        $camera = [
            'make' => $exif['Make'] ?? null,
            'model' => $exif['Model'] ?? null,
        ];

        $location = [
            'GPSLatitude' => $exif['GPSLatitude'] ?? null,
            'GPSLatitudeRef' => $exif['GPSLatitudeRef'] ?? null,
            'GPSLongitude' => $exif['GPSLongitude'] ?? null,
            'GPSLongitudeRef' => $exif['GPSLongitudeRef'] ?? null,
        ];

        return [
            'camera' => $camera,
            'location' => $location,
            'timestamp' => array_key_exists('DateTimeOriginal', $exif) ? strtotime($exif['DateTimeOriginal']) : null,
            'exposure' => $exif['ExposureTime'] ?? null,
            'aperture' => $exif['COMPUTED']['ApertureFNumber'] ?? null,
            'iso' => $exif['ISOSpeedRatings'] ?? null,
            'focalLength' => $exif['FocalLength'] ?? $exif['FocalLengthIn35mmFilm'] ?? null,
            'isColor' => $exif['COMPUTED']['IsColor'] ?? null,
        ];
    }

    /**
     * @return string[]
     */
    public function getMainColors(int $count = 5, int $delta = 32): array
    {
        $count = max(1, $count);
        $delta = max(2, $delta);
        $halfDelta = $delta / 2;
        $hexColors = [];
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $index = imagecolorat($this->image, $x, $y);
                $colors = imagecolorsforindex($this->image, $index);
                $colors['red'] = intval((($colors['red']) + $halfDelta) / $delta) * $delta;
                $colors['green'] = intval((($colors['green']) + $halfDelta) / $delta) * $delta;
                $colors['blue'] = intval((($colors['blue']) + $halfDelta) / $delta) * $delta;
                $colors['red'] = max(0, min(255, $colors['red']));
                $colors['green'] = max(0, min(255, $colors['green']));
                $colors['blue'] = max(0, min(255, $colors['blue']));
                $hex = '#'
                    . substr('0' . dechex($colors['red']), -2)
                    . substr('0' . dechex($colors['green']), -2)
                    . substr('0' . dechex($colors['blue']), -2);
                $hexColors[$hex] = isset($hexColors[$hex]) ? $hexColors[$hex] + 1 : 1;
            }
        }
        arsort($hexColors, SORT_NUMERIC);
        return array_slice(array_keys($hexColors), 0, $count);
    }

    public function getMainColor(): string
    {
        return $this->getMainColors()[0];
    }

    public function resize(int $width, int $height): self
    {
        $image = new Image(max(1, $width), max(1, $height));
        imagecopyresampled(
            $image->image, // dst
            $this->image, // src
            0, // dst x
            0, // dst y
            0, // src x
            0, // src y
            $image->width, // dst width
            $image->height, // dst height
            $this->width, // src width
            $this->height // src width
        );
        return $image;
    }

    public function copy(): self
    {
        $image = $this->resize($this->width, $this->height);
        $image->path = $this->path;
        return $image;
    }

    public function thumbnail(int $maxWidth, int $maxHeight): self
    {
        $maxWidth = max(1, min($this->width, $maxWidth));
        $maxHeight = max(1, min($this->height, $maxHeight));
        $width = $maxWidth;
        $height = $this->height / ($this->width / $maxWidth);
        if ($height > $maxHeight) {
            $width = $this->width / ($this->height / $maxHeight);
            $height = $maxHeight;
        }
        return $this->resize($width, $height);
    }

    public function crop(int $x1 = 0, int $y1 = 0, int $x2 = 0, int $y2 = 0): self
    {
        $x1 = max(0, min($this->width, $x1));
        $y1 = max(0, min($this->height, $y1));
        $x2 = max(0, min($this->width, $x2));
        $y2 = max(0, min($this->height, $y2));

        $w = max(1, $x2 - $x1);
        $h = max(1, $y2 - $y1);

        $image = new Image($w, $h);
        imagecopyresampled(
            $image->image, // dst
            $this->image, // src
            0, // dst x
            0, // dst y
            $x1, // src x
            $y1, // src y
            $image->width, // dst width
            $image->height, // dst height
            max(1, min($this->width, $x2 - $x1)), // src width
            max(1, min($this->height, $y2 - $y1)) // src height
        );
        return $image;
    }

    public function negate(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_NEGATE);
        return $image;
    }

    public function greyscale(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_GRAYSCALE);
        return $image;
    }

    public function brightness(int $level = 0): self
    {
        $image = $this->copy();
        $level = $level < -255 ? -255 : ($level > 255 ? 255 : $level);
        imagefilter($image->image, IMG_FILTER_BRIGHTNESS, $level);
        return $image;
    }

    public function contrast(int $level = 0): self
    {
        $image = $this->copy();
        $level = (int)$level;
        $level = $level < -100 ? -100 : ($level > 100 ? 100 : $level);
        imagefilter($image->image, IMG_FILTER_CONTRAST, $level);
        return $image;
    }

    public function colorize(int $red = 0, int $green = 0, int $blue = 0, int $alpha = 0): self
    {
        $image = $this->copy();
        $red = max(-255, min(255, $red));
        $green = max(-255, min(255, $green));
        $blue = max(-255, min(255, $blue));
        $alpha = max(-127, min(127, $alpha));
        if (!$alpha) {
            imagealphablending($image->image, false);
            imagesavealpha($image->image, true);
        }
        imagefilter($image->image, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
        return $image;
    }

    public function alpha(int $alpha = 0): self
    {
        $image = $this->copy();
        $alpha = max(-127, min(127, $alpha));
        imagealphablending($image->image, false);
        imagesavealpha($image->image, true);
        imagefilter($image->image, IMG_FILTER_COLORIZE, 0, 0, 0, $alpha);
        return $image;
    }

    public function edgeDetect(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_EDGEDETECT);
        return $image;
    }

    public function emboss(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_EMBOSS);
        return $image;
    }

    public function gaussianBlur(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_GAUSSIAN_BLUR);
        return $image;
    }

    public function selectiveBlur(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_SELECTIVE_BLUR);
        return $image;
    }

    public function meanRemoval(): self
    {
        $image = $this->copy();
        imagefilter($image->image, IMG_FILTER_MEAN_REMOVAL);
        return $image;
    }

    public function smooth(float $level = 0): self
    {
        $image = $this->copy();
        $level = max(0, min(2048, $level));
        imagefilter($image->image, IMG_FILTER_SMOOTH, $level);
        return $image;
    }

    public function pixelate(int $blockSize = 1, int|bool $mode = false): self
    {
        $image = $this->copy();
        $blockSize = max(1, $blockSize);
        $effect = !is_integer($mode) ? false : (int)$mode;
        imagefilter($image->image, IMG_FILTER_PIXELATE, $blockSize, $effect);
        return $image;
    }

    public function sharpen(array|null $sharpen = null, float|null $divisor = null, float $offset = 0): self
    {
        $image = $this->copy();
        $sharpen = $sharpen !== null ? $sharpen : [[0.0, -1.0, 0.0], [-1.0, 5.0, -1.0], [0.0, -1.0, 0.0]];
        $divisor = $divisor !== null ? $divisor : array_sum(array_map('array_sum', $sharpen));
        imageconvolution($image->image, $sharpen, $divisor, $offset);
        return $image;
    }

    public function background(int $red = 255, int $green = 255, int $blue = 255): self
    {
        $image = new self($this->width, $this->height);
        $red = max(-255, min(255, $red));
        $green = max(-255, min(255, $green));
        $blue = max(-255, min(255, $blue));
        $color = imagecolorallocate($image->image, $red, $green, $blue);
        imagefill($image->image, 0, 0, $color);
        imagecopyresampled(
            $image->image, // dst
            $this->image, // src
            0, // dst x
            0, // dst y
            0, // src x
            0, // src y
            $this->width, // src width
            $this->height, // src height
            $this->width, // dst width
            $this->height // dst height
        );
        return $image;
    }

    public function drawImage(Image|GdImage $image, int $dstX, int $dstY, int $srcX, int $srcY, int $dstWidth, int $dstHeight, int $srcWidth, int $srcHeight): self
    {
        imagecopyresampled(
            $this->image, // dst
            $image instanceOf Image ? $image->image : $image, // src
            $dstX, // dst x
            $dstY, // dst y
            $srcX, // src x
            $srcY, // src y
            $dstWidth, // dst width
            $dstHeight, // dst height
            $srcWidth, // src width
            $srcHeight, // src height
        );
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function flip(string $mode): self
    {
        if (!in_array($mode, ['h', 'v', 'b'])) {
            throw new InvalidArgumentException('Unknown flip mode (h,v,b)');
        }
        $image = $this->copy();
        $mode = $mode === 'h' ? IMG_FLIP_HORIZONTAL : $mode;
        $mode = $mode === 'v' ? IMG_FLIP_VERTICAL : $mode;
        $mode = $mode === 'b' ? IMG_FLIP_BOTH : $mode;
        imageflip($image->image, $mode);
        return $image;
    }

    public function rotate(int $degrees, int $red = 0, int $green = 0, int $blue = 0, int $alpha = 0): self
    {
        $image = $this->copy();
        $degrees = max(-360, min(360, $degrees)) * -1;
        $red = max(0, min(255, $red));
        $green = max(0, min(255, $green));
        $blue = max(0, min(255, $blue));
        $alpha = max(0, min(127, $alpha));
        if ($alpha) {
            imagealphablending($image->image, false);
            imagesavealpha($image->image, true);
        }
        $color = imageColorAllocateAlpha($image->image, $red, $green, $blue, $alpha);
        $image->image = imagerotate($image->image, $degrees, $color, false);
        $image->fixSizeInformation();
        return $image;
    }

    public function text(
        int $x = 0,
        int $y = 0,
        string $text = '',
        int $red = 0,
        int $green = 0,
        int $blue = 0,
        int $fontIndex = 0
    ): self {
        $image = $this->copy();
        $text = $text ? $text : '';
        $red = max(0, min(255, $red));
        $green = max(0, min(255, $green));
        $blue = max(0, min(255, $blue));
        $color = imagecolorallocate($image->image, $red, $green, $blue);
        imagestring($image->image, $fontIndex, $x, $y, $text, $color);
        return $image;
    }

    public function font(
        int $x = 0,
        int $y = 0,
        string $text = '',
        int $red = 0,
        int $green = 0,
        int $blue = 0,
        string $font = 'DejaVuSans'
    ): self {
        $image = $this->copy();
        $text = $text ? $text : '';
        $size = 20;
        $degrees = 0;
        $red = max(0, min(255, $red));
        $green = max(0, min(255, $green));
        $blue = max(0, min(255, $blue));
        $color = imagecolorallocate($image->image, $red, $green, $blue);
        imagettftext($image->image, $size, $degrees, $x, $y, $color, $font, $text);
        return $image;
    }

    /**
     * @throws RuntimeException
     */
    public function saveJpeg(string $path, int $quality = 100): self
    {
        $quality = (int)max(1, min($quality, 100));
        if (!imagejpeg($this->image, $path, $quality)) {
            throw new RuntimeException('Could not store jpeg to ' . $path);
        }
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function savePng(string $path, int $quality = 100): self
    {
        $quality = (int)round((100 - $quality) / 100 * 9, 0);
        if (!imagepng($this->image, $path, $quality)) {
            throw new RuntimeException('Could not store png to ' . $path);
        }
        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function saveGif(string $path): self
    {
        if (!imagegif($this->image, $path)) {
            throw new RuntimeException('Could not store gif to ' . $path);
        }
        return $this;
    }

    public function outputJpeg(int $quality = 100): string
    {
        ob_start();
        imagejpeg($this->image, null, $quality);
        return ob_get_clean();
    }

    public function outputPng(int $quality = 100): string
    {
        $quality = (int)round((100 - $quality) / 100 * 9, 0);
        ob_start();
        imagepng($this->image, null, $quality);
        return ob_get_clean();
    }

    public function outputGif(int $quality = 100): string
    {
        ob_start();
        imagegif($this->image, null);
        return ob_get_clean();
    }

    public function outputWebp(int $quality = 100): string
    {
        if (!function_exists('imagewebp')) {
            throw new RuntimeException('Function imagewebp doesn\'t exists.');
        }
        ob_start();
        imagewebp($this->image, null, $quality);
        return ob_get_clean();
    }

    private function allowAlpha(int $red = 255, int $green = 0, int $blue = 255): self
    {
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
        $bg = imagecolorallocatealpha($this->image, $red, $green, $blue, 127);
        imagecolortransparent($this->image, $bg);
        return $this;
    }

    private function fillAlpha(int $red = 255, int $green = 0, int $blue = 255): self
    {
        $bg = imagecolorallocatealpha($this->image, $red, $green, $blue, 127);
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $bg);
        return $this;
    }
}
