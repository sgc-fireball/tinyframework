<?php

declare(strict_types=1);

namespace TinyFramework\Cache;

use Swoole\Table;

class SwooleTableCache extends Table
{
    public function __construct()
    {
        parent::__construct(10240, 0);
        $this->column('key', Table::TYPE_STRING, 2048);
        $this->column('value', Table::TYPE_STRING, 65535);
        $this->column('expires_at', Table::TYPE_INT, 32);
        $this->create();
    }
}
