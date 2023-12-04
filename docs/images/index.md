# Images

## Usage

```php
$image = new \TinyFramework\Helpers\Image(1024, 1024);
$image = \TinyFramework\Helpers\Image::createFromImage('/path/to/image.ext');
$image = \TinyFramework\Helpers\Image::createFromBmp('/path/to/image.bmp');
$image = \TinyFramework\Helpers\Image::createFromJpeg('/path/to/image.jpg');
$image = \TinyFramework\Helpers\Image::createFromWebp('/path/to/image.webpo');
$image = \TinyFramework\Helpers\Image::createFromString(file_get_content('/path/to/image.ext'));
$image = \TinyFramework\Helpers\Image::createFromPng('/path/to/image.png');
$image = \TinyFramework\Helpers\Image::createFromGif('/path/to/image.gif');
$image = \TinyFramework\Helpers\Image::createFromWbmp('/path/to/image.wbmp');
$image = \TinyFramework\Helpers\Image::createFromXbm('/path/to/image.xbm');
$image = \TinyFramework\Helpers\Image::createFromXpm('/path/to/image.xpm');
$image = \TinyFramework\Helpers\Image::createFromGd('/path/to/image.gd');
$image = \TinyFramework\Helpers\Image::createFromGd2('/path/to/image.gd2');
```

