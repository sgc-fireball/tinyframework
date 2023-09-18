<?php

declare(strict_types=1);

namespace TinyFramework\Http;

class DownloadResponse extends Response
{
    private string $file;

    private int $fileSize;

    public function __construct(string $file)
    {
        parent::__construct();
        $this->file = $file;
        if (!is_file($file) || !is_readable($file)) {
            throw new \RuntimeException('The response file could not be readed or does not exists.');
        }
        $this->fileSize = (int)filesize($this->file);
    }

    public function send(): static
    {
        $this->content('');
        $request = container('request');
        assert($request instanceof Request);

        $start = 0;
        $end = $this->fileSize - 1;
        $this->header('Accept-Range', 'bytes');
        if ($range = $request->header('Range')[0]) {
            if (!str_contains($range, 'bytes=')) {
                $this
                    ->code(416)
                    ->content('Invalid range header.')
                    ->header('Content-Range', sprintf('*/%d', $this->fileSize));
                return parent::send();
            }
            [, $range] = explode('=', $range);
            if (str_contains($range, ',')) {
                $this
                    ->code(416)
                    ->content('Invalid range formation.')
                    ->header('Content-Range', sprintf('*/%d', $this->fileSize));
                return parent::send();
            }
            [$reqStart, $reqEnd] = explode('-', $range);
            if ($reqStart < 0 || $reqEnd <= $reqStart || $end < $reqEnd) {
                $this
                    ->code(416)
                    ->content('Invalid range size.')
                    ->header('Content-Range', sprintf('*/%d', $this->fileSize));
                return parent::send();
            }

            $this->code(206);
            $this->header('Content-Length', (string)($end - $start));
            $this->header('Content-Range', sprintf('%d-%d/%d', $start, $end, $this->fileSize));
            $start = (int)$reqStart;
            $end = (int)$reqEnd;
        } else {
            $this->header('Content-Length', (string)$this->fileSize);
        }
        $this
            ->type(mime_content_type($this->file) ?: 'application/octet-stream')
            ->header('Content-Disposition', sprintf('attachment; filename="%s"', basename($this->file)));

        parent::send();
        if ($request->method() === 'GET') {
            $fp = fopen($this->file, 'rb');
            $rounds = ceil(($end - $start) / 1024);
            fseek($fp, $start);
            for ($i = $rounds; $i > 0; $i--) {
                echo fread($fp, 1024);
                flush();
            }
            fclose($fp);
        }
        return $this;
    }
}
