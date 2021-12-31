<?php

namespace TinyFramework\Queue;

class TestJob extends JobAwesome
{
    protected string $queue = 'low';

    public function tryHandle(): void
    {
        container('logger')->info(__CLASS__);
        sleep(1);
    }
}
