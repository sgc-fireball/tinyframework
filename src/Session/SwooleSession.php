<?php

declare(strict_types=1);

namespace TinyFramework\Session;

class SwooleSession extends SessionAwesome implements SessionInterface
{
    private SwooleTableSession $table;

    private array $config = [];
    
    public function __construct(SwooleTableSession $table, #[\SensitiveParameter] array $config = [])
    {
        $this->table = $table;
        $this->config['ttl'] = (int)($config['ttl'] ?? 300);
    }

    public function open(?string $id): static
    {
        $this->data = [];
        $this->id = $id ?: $this->newId();
        $value = $this->table->get($this->getId());
        if (!$value) {
            return $this;
        }
        if ($value['expires_at'] < time()) {
            $this->table->del($id);
            return $this;
        }
        $this->data = (array)unserialize($value['context']);
        return $this;
    }

    public function close(): static
    {
        $this->table->set($this->getId(), [
            'id' => $this->getId(),
            'context' => serialize($this->data),
            'expires_at' => time() + $this->config['ttl'],
        ]);
        $this->data = [];
        return $this;
    }

    public function destroy(): static
    {
        if ($this->table->exist($this->getId())) {
            $this->table->del($this->getId());
        }
        $this->data = [];
        return $this;
    }

    public function clear(): static
    {
        $ids = [];
        foreach ($this->table as $value) {
            if ($value['expires_at'] < time()) {
                $ids[] = $value['id'];
            }
        }
        foreach ($ids as $id) {
            $this->table->del($id);
        }
        $this->data = [];
        return $this;
    }
}
