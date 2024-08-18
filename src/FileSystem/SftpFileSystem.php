<?php

declare(strict_types=1);

namespace TinyFramework\FileSystem;

use TinyFramework\Exception\FileSystemException;

class SftpFileSystem extends FileSystemAwesome implements FileSystemInterface
{

    /** @var resource|null */
    protected $sshSocket = null;

    /** @var resource|null */
    protected $sftpSocket = null;

    protected array $config = [
        'username' => null,
        'pubkeyfile' => null,
        'privkeyfile' => null,
        'password' => null,
        'host' => null,
        'port' => 22,
        'basePath' => '/',
        'filePermission' => 0640,
        'folderPermission' => 0750,
    ];

    private function buildLocation(string $location): string
    {
        return sprintf(
            '%s%s',
            $this->config['basePath'],
            ltrim($location, '/')
        );
    }

    private function prefixLocation(string $location): string
    {
        return sprintf(
            'ssh2.sftp://%d%s',
            intval($this->sftpSocket),
            $location
        );
    }

    public function __construct(array $config)
    {
        $basePath = '/' . trim($config['basePath'] ?? '/', '/') . '/';
        $basePath = $basePath === '/' . '/' ? '/' : $basePath;
        $this->config = array_merge($this->config, $config, ['basePath' => $basePath]);
        if (!extension_loaded('ssh2')) {
            $this->throw('Missing ext-ssh2.');
        }
    }

    public function __destruct()
    {
        if ($this->sshSocket) {
            @ssh2_disconnect($this->sshSocket);
            $this->sshSocket = null;
        }
        $this->sftpSocket = null;
    }

    private function connect(): self
    {
        if (!$this->sshSocket) {
            $this->sshSocket = @ssh2_connect($this->config['host'], $this->config['port']);
            if (!$this->sshSocket) {
                $this->sshSocket = null;
                $this->throw('Could not connect.');
            }
            if ($this->config['password'] && (!$this->config['privkeyfile'] || !$this->config['pubkeyfile'])) {
                if (!ssh2_auth_password($this->sshSocket, $this->config['username'], $this->config['password'])) {
                    $this->throw('Could not login via password.');
                }
            } elseif ($this->config['privkeyfile'] && $this->config['pubkeyfile']) {
                if (!ssh2_auth_pubkey_file(
                    $this->sshSocket,
                    $this->config['username'],
                    $this->config['pubkeyfile'],
                    $this->config['privkeyfile'],
                    $this->config['password']
                )) {
                    $this->throw('Could not login via private key.');
                }
            }
            $this->sftpSocket = ssh2_sftp($this->sshSocket);
            if (!$this->sftpSocket) {
                $this->sftpSocket = null;
                $this->sshSocket = null;
                $this->throw('Could switch to sftp protocol.');
            }
        }
        return $this;
    }

    public function fileExists(string $location): bool
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        return is_file($_path);
    }

    public function directoryExists(string $location): bool
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        return is_dir($_path);
    }

    public function exists(string $location): bool
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        return file_exists($_path);
    }

    public function write(string $location, mixed $contents, array $config = []): self
    {
        try {
            $fp = fopen('data://text/plain,' . $contents, 'r');
            $this->writeStream($location, $fp, $config);
        } finally {
            isset($fp) && is_resource($fp) && fclose($fp);
        }
        return $this;
    }

    public function writeStream(string $location, mixed $contents, array $config = []): self
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        $stream = fopen($_path, 'w+');
        while (!feof($contents) && $content = fread($contents, 4096)) {
            if (strlen($content) && !@fwrite($stream, $content)) {
                $this->throw('Could not write file chunk.', $_location);
            }
        }

        if ($this->config['filePermission'] !== null) {
            if (@ssh2_sftp_chmod($this->sftpSocket, $_location, $this->config['filePermission']) === false) {
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
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        return fopen($_path, 'r');
    }

    public function delete(string $location): self
    {
        $this->connect();
        if ($this->fileExists($location)) {
            $_location = $this->buildLocation($location);
            if (!@ssh2_sftp_unlink($this->sftpSocket, $_location)) {
                $this->throw('Could not remove file.', $_location);
            }
            if ($this->fileExists($location)) {
                throw new \RuntimeException('Could not delete the file!!!!');
            }
        } elseif ($this->directoryExists($location)) {
            $fileList = $this->list($location);
            foreach ($fileList as $subLocation) {
                $this->delete($location . '/' . $subLocation);
            }
            $_location = $this->buildLocation($location);
            if (!@ssh2_sftp_rmdir($this->sftpSocket, $_location)) {
                $this->throw('Could not remove directory.', $_location);
            }
        }
        return $this;
    }

    public function createDirectory(string $location, array $config = []): self
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        if ($this->directoryExists($location)) {
            return $this;
        }
        if (@ssh2_sftp_mkdir($this->sftpSocket, $_location, $this->config['folderPermission'], true) === false) {
            $this->throw('Could not create folder.', $_location);
        }
        return $this;
    }

    public function list(string $location): mixed
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        if (!($handle = opendir($_path))) {
            $this->throw('Could not receive file list.', $location);
        }
        $result = [];
        while (false !== ($entry = readdir($handle))) {
            $result[] = $entry;
        }
        return array_filter($result, fn(string $path) => !in_array($path, ['.', '..']));
    }

    public function move(string $source, string $destination, array $config = []): self
    {
        $this->connect();
        $_source = $this->buildLocation($source);
        $_destination = $this->buildLocation($destination);
        $this->delete($destination); // in sftp we could not "overmove" a file, that already exists
        if (!@ssh2_sftp_rename($this->sftpSocket, $_source, $_destination)) {
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
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        $size = @filesize($_path);
        if ($size === false) {
            $this->throw('Could not read size.', $_location);
        }
        return $size;
    }

    public function mimeType(string $location): string
    {
        $this->connect();
        $_location = $this->buildLocation($location);
        $_path = $this->prefixLocation($_location);
        clearstatcache(true, $_path);
        $mimeType = @mime_content_type($_path);
        if (!$mimeType) {
            $this->throw('Could not detect mimetype.', $_location);
        }
        return $mimeType;
    }

    /**
     * @throws FileSystemException
     */
    public function url(string $location): string
    {
        $_location = $this->buildLocation($location);
        $this->throw('Filesystem does not support url downloads.', $_location);
    }

    /**
     * @throws FileSystemException
     */
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
    private function throw(string $message, string|null $location = null, string|null $destination = null): never
    {
        if ($location) {
            $message .= sprintf(
                ' (sftp://%s%s:%d/%s)',
                $this->config['username'] ? $this->config['username'] . '@' : '',
                $this->config['host'],
                $this->config['port'],
                ltrim($location, '/')
            );
        }
        if ($destination) {
            $message .= sprintf(
                ' (sftp://%s%s:%d/%s)',
                $this->config['username'] ? $this->config['username'] . '@' : '',
                $this->config['host'],
                $this->config['port'],
                ltrim($destination, '/')
            );
        }
        throw new FileSystemException($message);
    }
}
