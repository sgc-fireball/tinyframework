<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use RuntimeException;

class Video
{

    private static bool $checked = false;

    private array $cleanUp = [];

    public static function createFromFile(string $path): static
    {
        return new self($path);
    }

    public function __construct(
        private string $path
    ) {
        if (!self::$checked) {
            if (!command_exists('ffmpeg')) {
                throw new RuntimeException('Please install ffmpeg first or fix the $PATH.');
            }
            if (!command_exists('ffprobe')) {
                throw new RuntimeException('Please install ffprobe first or fix the $PATH.');
            }
            self::$checked = true;
        }
    }

    /**
     * @link https://ffmpeg.org/ffmpeg-utils.html#Time-duration
     * @param string $second
     * @return Image
     */
    public function frame(string|int $second = 5): Image
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'tinyframework-video');
        $command = sprintf(
            'ffmpeg -y -ss %s -i %s -f mjpeg -vframes 1 %s -v quiet',
            escapeshellarg((string)$second),
            escapeshellarg($this->path),
            escapeshellarg($outputPath)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not create thumbnail from ' . $this->path);
        }
        return Image::createFromImage($outputPath);
    }

    public function duration(): int
    {
        $command = sprintf(
            'ffprobe -i %s -show_format -v quiet | sed -n \'s/duration=//p\'',
            escapeshellarg($this->path)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not create thumbnail from ' . $this->path);
        }
        return (int)round((float)implode('', $output));
    }

    public function width(): int
    {
        $command = sprintf(
            'ffprobe -v quiet -show_entries stream=width -of default=noprint_wrappers=1 %s | sed -n \'s/width=//p\'',
            escapeshellarg($this->path)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not read width from ' . $this->path);
        }
        return (int)implode('', $output);
    }

    public function height(): int
    {
        $command = sprintf(
            'ffprobe -v quiet -show_entries stream=height -of default=noprint_wrappers=1 %s | sed -n \'s/height=//p\'',
            escapeshellarg($this->path)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not read height from ' . $this->path);
        }
        return (int)implode('', $output);
    }

    public function fps(): int
    {
        $command = sprintf(
            'ffprobe -v quiet -show_entries stream=avg_frame_rate -of default=noprint_wrappers=1 %s | head -n 1 | sed -n \'s/avg_frame_rate=//p\'',
            escapeshellarg($this->path)
        );
        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not read fps from ' . $this->path);
        }
        [$val1, $val2] = explode('/', implode('', $output));
        return (int)round((intval($val1, 10) / intval($val2, 10)), 0);
    }

    public function __destruct()
    {
        foreach ($this->cleanUp as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

}
