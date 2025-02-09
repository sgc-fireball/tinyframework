<?php

declare(strict_types=1);

namespace TinyFramework\Session;

use Swoole\Table;

class SwooleTableSession extends Table
{

    public function __construct()
    {
        parent::__construct(10240, 0);
        $this->column('id', Table::TYPE_STRING, 36);
        $this->column('context', Table::TYPE_STRING, 65535);
        $this->column('expires_at', Table::TYPE_STRING, 32);
        $this->create();
    }
}
