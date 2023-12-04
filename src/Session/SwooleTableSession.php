<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use Swoole\Table;

class SwooleTableSession extends SessionAwesome implements SessionInterface
{
    private array $config = [];

    private Table $table;

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        $this->config['ttl'] = (int)($config['ttl'] ?? 300);

        $this->table = new Table(10240);
        $this->table->column('id', Table::TYPE_STRING, 36);
        $this->table->column('context', Table::TYPE_STRING, 65535);
        $this->table->column('expires_at', Table::TYPE_STRING, 32);
        $this->table->create();
    }

    public function open(?string $id): static
    {
        $this->data = [];
        $this->id = $id ?: guid();
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
