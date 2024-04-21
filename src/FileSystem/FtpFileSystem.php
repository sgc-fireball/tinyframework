<?php

declare(strict_types=1);

namespace TinyFramework\FileSystem;

use TinyFramework\Exception\FileSystemException;

class FtpFileSystem extends FileSystemAwesome implements FileSystemInterface
{
    /** @var \FTP\Connection|resource|null */
    protected $socket = null;

    protected array $config = [
        'username' => null,
        'password' => null,
        'host' => null,
        'port' => 21,
        'ssl' => false,
        'basePath' => '/',
        'passiv' => false,
        'timeout' => 5,
        'folderPermission' => 0750,
        'filePermission' => 0640,
    ];

    private function buildLocation(string $location): string
    {
        return sprintf(
            '%s%s',
            $this->config['basePath'],
            ltrim($location, '/')
        );;
    }

    public function __construct(array $config)
    {
        $basePath = '/' . trim($config['basePath'] ?? '/', '/') . '/';
        $basePath = $basePath === '/' . '/' ? '/' : $basePath;
        $this->config = array_merge($this->config, $config, ['basePath' => $basePath]);
    }

    public function __destruct()
    {
        if ($this->socket) {
            @ftp_close($this->socket);
            $this->socket = null;
        }
    }

    private function connect(): self
    {
        if (!$this->socket) {
            if (!extension_loaded('ftp')) {
                $this->throw('Missing ext-ftp.');
            }
            $func = $this->config['ssl'] ? 'ftp_ssl_connect' : 'ftp_connect';
            $this->socket = @$func(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
            if (!$this->socket) {
                $this->socket = null;
                $this->throw('Could not connect.');
            }
            if ($this->config['username']) {
                if (!@ftp_login($this->socket, $this->config['username'], $this->config['password'])) {
                    $this->throw('Could not login');
                }
            }
            if ($this->config['passiv']) {
                if (@ftp_pasv($this->socket, true) !== true) {
                    $this->throw('Could not switch to passiv mode.');
                }
            }
            if (!ftp_chdir($this->socket, $this->config['basePath'])) {
                $this->throw('Could not change directory.', $this->config['basePath']);
            }
        }
        return $this;
    }

    public function fileExists(string $location): bool
    {
        try {
            $_location = $this->buildLocation($location);
            $_folder = dirname($_location);
            $list = @ftp_nlist($this->socket, $_folder);
            if (!is_array($list) || !in_array(basename($_location), $list)) {
                return false;
            }

            $result = @ftp_chdir($this->socket, $_location);
            if ($result && !@ftp_chdir($this->socket, $this->config['basePath'])) {
                $this->throw('Could not change directory.', $this->config['basePath']);
            }
            return $result === false;
        } catch (\Throwable) {
            return false;
        }
    }

    public function directoryExists(string $location): bool
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_folder = dirname($_location);
        $list = @ftp_nlist($this->socket, $_folder);
        if (!is_array($list) || !in_array(basename($_location), $list)) {
            return false;
        }

        $result = @ftp_chdir($this->socket, $_location);
        if ($result && !@ftp_chdir($this->socket, $this->config['basePath'])) {
            $this->throw('Could not change directory.', $this->config['basePath']);
        }
        return $result === true;
    }

    public function exists(string $location): bool
    {
        return $this->fileExists($location) || $this->directoryExists($location);
    }

    public function write(string $location, $contents, array $config = []): self
    {
        try {
            $fp = fopen('data://text/plain,' . $contents, 'r');
            $this->writeStream($location, $fp, $config);
        } finally {
            isset($fp) && is_resource($fp) && fclose($fp);
        }
        return $this;
    }

    public function writeStream(string $location, $contents, array $config = []): self
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        if (!@ftp_fput($this->socket, $_location, $contents)) {
            $this->throw('Could not write file.', $_location);
        }
        if ($this->config['filePermission'] !== null) {
            if (@ftp_chmod($this->socket, $this->config['filePermission'], $_location) === false) {
                $this->throw('Could not change file mode.', $_location);
            }
        }
        return $this;
    }

    public function read(string $location): string
    {
        $readStream = $this->readStream($location);
        $contents = stream_get_contents($readStream);
        fclose($readStream);
        return $contents;
    }

    public function readStream(string $location): mixed
    {
        $this->connect();
        $stream = fopen('php://temp', 'w+b');
        $_location = $this->buildLocation($location);
        $result = @ftp_fget($this->socket, $stream, $_location);
        if (!$result) {
            fclose($stream);
            $this->throw('Could not read file.', $_location);
        }
        rewind($stream);
        return $stream;
    }

    public function delete(string $location): self
    {
        $this->connect();
        if ($this->fileExists($location)) {
            $_location = $this->buildLocation($location);
            if (@ftp_delete($this->socket, $_location) === false) {
                $this->throw('Could not remove file.', $_location);
            }
        } elseif ($this->directoryExists($location)) {
            $fileList = $this->list($location);
            foreach ($fileList as $subLocation) {
                $this->delete($subLocation);
            }
            $_location = $this->buildLocation($location);
            $result = @ftp_rmdir($this->socket, $_location);
            if ($result === false) {
                $this->throw('Could remove directory.', $_location);
            }
        }
        return $this;
    }

    public function createDirectory(string $location, array $config = []): self
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        if (@ftp_mkdir($this->socket, $_location) === false) {
            $this->throw('Could not create folder.', $_location);
        }
        if ($this->config['folderPermission'] !== null) {
            if (@ftp_chmod($this->socket, $this->config['folderPermission'], $_location) === false) {
                $this->throw('Could not change dir mode.', $_location);
            }
        }
        return $this;
    }

    public function list(string $location): mixed
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $result = @ftp_nlist($this->socket, $_location);
        if ($result !== false) {
            return array_filter($result, fn(string $path) => !in_array($path, ['.', '..']));
        }
        $this->throw('Could not receive file list.', $location);
    }

    public function move(string $source, string $destination, array $config = []): self
    {
        $this->connect();
        $_source = $this->buildLocation($source);
        $_destination = $this->buildLocation($destination);
        if (!@ftp_rename($this->socket, $_source, $_destination)) {
            $this->throw('Could not move file.', $_source, $_destination);
        }
        return $this;
    }

    public function copy(string $source, string $destination, array $config = []): self
    {
        try {
            $readStream = $this->readStream($source);
            $this->writeStream($destination, $readStream, $config);
        } finally {
            if (isset($readStream) && is_resource($readStream)) {
                @fclose($readStream);
            }
        }
        return $this;
    }

    public function fileSize(string $location): int
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $size = @ftp_size($this->socket, $_location);
        if ($size === -1) {
            $this->throw('Could not read size.', $_location);
        }
        return $size;
    }

    public function mimeType(string $location): string
    {
        $this->throw('Unsupported on ftp.');
    }

    public function url(string $location): string
    {
        $_location = $this->buildLocation($location);
        if ($this->config['username']) {
            $this->throw('Filesystem does not support url downloads.', $_location);
        }
        return sprintf(
            'ftp%s://%s%s:%d/%s',
            $this->config['ssl'] ? 's' : '',
            $this->config['username'] ? $this->config['username'] . '@' : '',
            $this->config['host'],
            $this->config['port'],
            ltrim($location, '/')
        );
    }

    public function temporaryUrl(string $location, int $ttl, array $config = []): string
    {
        $_location = $this->buildLocation($location);
        $this->throw('Filesystem does not support temporary url downloads.', $_location);
    }

    /**
     * @param string $message
     * @param string|null $location
     * @param string|null $destination
     * @throws FileSystemException
     */
    private function throw(string $message, string|null $location = null, string|null $destination = null): void
    {
        if ($location) {
            $message .= sprintf(
                ' (ftp%s://%s%s:%d/%s)',
                $this->config['ssl'] ? 's' : '',
                $this->config['username'] ? $this->config['username'] . '@' : '',
                $this->config['host'],
                $this->config['port'],
                ltrim($location, '/')
            );
        }
        if ($destination) {
            $message .= sprintf(
                ' (ftp%s://%s%s:%d/%s)',
                $this->config['ssl'] ? 's' : '',
                $this->config['username'] ? $this->config['username'] . '@' : '',
                $this->config['host'],
                $this->config['port'],
                ltrim($destination, '/')
            );
        }
        throw new FileSystemException($message);
    }
}
