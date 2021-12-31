<?php

declare(strict_types=1);

namespace TinyFramework\Http;

class UploadedFile
{
    private array $file = [
        'name' => null,
        'type' => null,
        'size' => 0,
        'tmp_name' => 0,
        'error' => 0,
    ];

    public function __construct(array $file)
    {
        $this->file = array_merge($this->file, $file);
    }

    public function size(): int
    {
        return $this->file['size'];
    }

    public function filename(): int
    {
        return $this->file['name'];
    }

    public function filepath(): int
    {
        return $this->file['tmp_name'];
    }

    public function extension(): string
    {
        return pathinfo($this->file['name'], PATHINFO_EXTENSION);
    }

    public function mimetype(): string
    {
        return mime_content_type($this->file['tmp_name']) ?? 'application/octet-stream';
    }

    public function hasError(): bool
    {
        return $this->file['error'] !== UPLOAD_ERR_OK;
    }

    public function move(string $target): bool
    {
        if (!is_uploaded_file($this->file['tmp_name'])) {
            return false;
        }
        if ($result = move_uploaded_file($this->file['tmp_name'], $target)) {
            @chmod($target, 0666 & ~umask());
        }
        return $result;
    }
}
