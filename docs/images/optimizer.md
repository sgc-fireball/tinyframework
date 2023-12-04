# ImageOptimizer

## Usage

```php
\TinyFramework\Helpers\ImageOptimizer::optimize('/path/to/image.ext')
\TinyFramework\Helpers\ImageOptimizer::optimizeJpeg('/path/to/image.jpeg')
\TinyFramework\Helpers\ImageOptimizer::optimizePng('/path/to/image.png')
\TinyFramework\Helpers\ImageOptimizer::optimizeSvg('/path/to/image.svg')
\TinyFramework\Helpers\ImageOptimizer::optimizeGif('/path/to/image.gif')
\TinyFramework\Helpers\ImageOptimizer::optimizeWebP('/path/to/image.webp')
```

## Optimizer

### WebP

- cwebp

### GIF

- gifsicle

### SVG

- svgo

### PNG

- optipng
- pngquant

### JPEG

- jpegoptim
