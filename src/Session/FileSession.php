<?php declare(strict_types=1);

namespace TinyFramework\Session;

class FileSession extends SessionAwesome implements SessionInterface
{

    private string $path;

    public function __construct(array $config = [])
    {
        $this->path = $config['path'] ?? sys_get_temp_dir();
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0770, true)) {
                throw new \RuntimeException('Could not create session folder.');
            }
        }
        if (!is_readable($this->path) || !is_writable($this->path)) {
            throw new \RuntimeException('Invalid session folder permission.');
        }
        $this->ttl = (int)$config['ttl'] ?? $this->ttl;
    }

    public function open(?string $id): SessionInterface
    {
        if ($id) {
            $this->id = $id;
        }
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (!file_exists($file)) {
            return $this;
        }
        if (!is_readable($file)) {
            throw new \RuntimeException('Could not read session file.');
        }
        $this->data = unserialize(file_get_contents($file));
        return $this;
    }

    public function clear(): SessionInterface
    {
        foreach (glob(sprintf('%s/*.session.tmp', $this->path)) as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        return $this;
    }

    public function close(): SessionInterface
    {
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (!is_writable($this->path)) {
            throw new \RuntimeException('Could not save session file. (1)');
        }
        if (file_exists($file) && !is_writable($file)) {
            throw new \RuntimeException('Could not save session file. (2)');
        }
        if (file_put_contents($file, serialize($this->data)) === false) {
            throw new \RuntimeException('Could not save session file. (3)');
        }
        return $this;
    }

    public function destroy(): SessionInterface
    {
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (file_exists($file)) {
            if (!is_writable($file)) {
                throw new \RuntimeException('Could not delete session file.');
            }
            unlink($file);
        }
        return $this;
    }

}
