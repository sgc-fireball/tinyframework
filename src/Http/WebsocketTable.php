<?php

declare(strict_types=1);

namespace TinyFramework\Http;

use Swoole\Table;

class WebsocketTable extends Table
{
    public function __construct()
    {
        parent::__construct(10240, 0);
        $this->column('id', Table::TYPE_INT);
        $this->column('request', Table::TYPE_STRING, 65535);
        $this->create();
    }
}
