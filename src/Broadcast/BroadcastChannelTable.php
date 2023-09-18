<?php

declare(strict_types=1);

namespace TinyFramework\Broadcast;

use Swoole\Table;

class BroadcastChannelTable extends Table
{
    public function __construct()
    {
        parent::__construct(10240, 0);
        $this->column('fd', Table::TYPE_INT);
        $this->column('channel', Table::TYPE_STRING, 255);
        $this->create();
    }

    public function buildKey(int $fd, string $channel): string
    {
        return sprintf('%d:%s', $fd, $channel);
    }

    public function isAllowed(int $fd, string $channel): bool
    {
        return $this->exists($this->buildKey($fd, $channel));
    }

    public function allow(int $fd, string $channel): void
    {
        $this->set(
            $this->buildKey($fd, $channel),
            ['fd' => $fd, 'channel' => $channel]
        );
    }

    public function disallow(int $fd, string $channel): void
    {
        $key = $this->buildKey($fd, $channel);
        if ($this->exist($key)) {
            $this->delete($key);
        }
    }

    public function cleanup(int $fd): void
    {
        $deleteKeys = [];
        foreach ($this as $key => $value) {
            if ($value['fd'] === $fd) {
                $deleteKeys[] = $key;
            }
        }
        foreach ($deleteKeys as $key) {
            $this->delete($key);
        }
    }

    public function getFdByChannel(string $channel): array
    {
        $ids = [];
        foreach ($this as $key => $value) {
            if ($value['channel'] === $channel) {
                $ids = $value['fd'];
            }
        }
        return $ids;
    }
}
