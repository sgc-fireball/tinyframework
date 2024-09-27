<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use RuntimeException;

class Video
{

    use Macroable;

    public const RESOLUTION_240P = 240;
    public const RESOLUTION_360P = 360;
    public const RESOLUTION_480P = 480;
    public const RESOLUTION_720P = 720;
    public const RESOLUTION_1080P = 1080;
    public const RESOLUTION_1440P = 1440;

    private static bool $checked = false;

    public static function createFromFile(string $path): self
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

    /**
     * @see https://developer.bitmovin.com/playback/docs/webvtt-based-thumbnails
     * @see https://bitmovin.com/demos/thumbnail-seeking
     * @param int $second default is zero, that means we are creating automatically 100 positions, but maximal one per second.
     * @return array<Image, string>
     */
    public function webvtt(string $target, int $second = 0, int $width = 120): array
    {
        $content = "WEBVTT";
        $duration = $this->duration();
        if ($second < 1) {
            $second = max(1, (int)($duration / 100));
        }
        $images = min(100, (int)($duration / $second));
        $ratio = $width / $this->width();
        $height = (int)($this->height() * $ratio);
        $webVttImage = new Image($width, $height * $images);
        for ($position = 0; $position < $images; $position += 1) {
            $time = $position * $second;
            $offsetY = $position * $height;
            $content .= sprintf(
                "\n\n%s.000 --> %s.000\n%s#xywh=0,%d,%d,%d",
                $this->secondsToHis($time),
                $this->secondsToHis($time + $second),
                $target,
                $offsetY,
                $width,
                $height
            );
            $frame = $this->frame($time);
            $webVttImage->drawImage(
                $frame,
                0,
                $offsetY,
                0,
                0,
                $width,
                $height,
                $frame->getWidth(),
                $frame->getHeight()
            );
        }
        return [$webVttImage, $content];
    }

    private function secondsToHis(int $seconds): string
    {
        return sprintf('%02d:%02d:%02d', ($seconds / 3600), ($seconds / 60 % 60), $seconds % 60);
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

    /**
     * @link https://support.google.com/youtube/answer/2853702?hl=de
     */
    public function resolution(int $resolutions, string $target): self
    {
        [$videoBitrate, $audioChannels, $audioSample, $audioBitrate] = match ($resolutions) {
            self::RESOLUTION_240P => ['500k', 1, 48, '128k'], // mono 48khz
            self::RESOLUTION_360P => ['1000k', 1, 48, '128k'], // mono 48khz
            self::RESOLUTION_480P => ['2500k', 1, 48, '128k'], // mono 48khz
            self::RESOLUTION_720P => ['5000k', 2, 96, '384k'], // stereo 96khz
            self::RESOLUTION_1080P => ['8000k', 2, 96, '384k'], // stereo 96khz
            self::RESOLUTION_1440P => ['16000k', 2, 96, '384k'], // stereo 96khz
            default => throw new \InvalidArgumentException('Invalid RESOLUTION value.')
        };

        $width = ceil($this->width() / $this->height() * $resolutions);
        $width -= $width % 2;

        $command = [];
        $command[] = sprintf('ffmpeg -y -i %s', escapeshellarg($this->path));
        $command[] = '-v quiet'; // suppress output
        // audio settings
        $command[] = '-c:a aac'; // codec
        $command[] = sprintf('-ac %d', $audioChannels); // audio channels
        $command[] = sprintf('-ar %d', $audioSample * 1000); // sampling rate (in Hz)
        $command[] = sprintf('-b:a %s', $audioBitrate); // bitrate
        // video settings
        $command[] = '-c:v libx264'; // codec
        $command[] = sprintf('-b:v %s', $videoBitrate); // bitrate
        $command[] = '-f flv'; // format
        $command[] = '-g 25'; // group size of pictures
        $command[] = '-r 25'; // fps
        $command[] = sprintf('-s %dx%d', $width, $resolutions); // resolution
        $command[] = '-preset ultrafast'; // converter profile
        // output file
        $command[] = escapeshellarg($target);
        $command = implode(' ', $command);

        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('Could not read fps from ' . $this->path);
        }
        return $this;
    }

}
