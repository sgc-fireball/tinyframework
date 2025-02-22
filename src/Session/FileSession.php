<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use RuntimeException;

class FileSession extends SessionAwesome implements SessionInterface
{
    private string $path;

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->path = $config['path'] ?? sys_get_temp_dir();
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0750, true)) {
                throw new RuntimeException('Could not create session folder.');
            }
        }
        if (!is_readable($this->path) || !is_writable($this->path)) {
            throw new RuntimeException('Invalid session folder permission.');
        }
        $this->ttl = (int)$config['ttl'] ?: $this->ttl;
    }

    public function open(string $id = null): static
    {
        $this->data = [];
        $this->id = $id ?: $this->newId();
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (!file_exists($file)) {
            return $this;
        }
        if (!is_readable($file)) {
            throw new RuntimeException('Could not read session file.');
        }
        $this->data = (array)unserialize((string)file_get_contents($file));
        return $this;
    }

    public function count(): int
    {
        return count((array)glob(sprintf('%s/*.session.tmp', $this->path)));
    }

    public function clear(): static
    {
        foreach ((array)glob(sprintf('%s/*.session.tmp', $this->path)) as $file) {
            if (\is_string($file) && file_exists($file) && is_writable($file)) {
                unlink($file);
            }
        }
        $this->data = [];
        return $this;
    }

    public function close(): static
    {
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (!is_writable($this->path)) {
            throw new RuntimeException('Could not save session file. (1)');
        }
        if (file_exists($file) && !is_writable($file)) {
            throw new RuntimeException('Could not save session file. (2)');
        }
        if (file_put_contents($file, serialize($this->data)) === false) {
            throw new RuntimeException('Could not save session file. (3)');
        }
        if (!chmod($file, 0600)) {
            throw new RuntimeException('Could not set chmod on session file. (4)');
        }
        $this->data = [];
        return $this;
    }

    public function destroy(): static
    {
        $file = sprintf('%s/%s.session.tmp', $this->path, $this->getId());
        if (file_exists($file)) {
            if (!is_writable($file)) {
                throw new RuntimeException('Could not delete session file.');
            }
            unlink($file);
        }
        $this->data = [];
        return $this;
    }
}
