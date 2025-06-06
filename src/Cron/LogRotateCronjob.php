<?php

declare(strict_types=1);

namespace TinyFramework\Cron;

use TinyFramework\Logger\LogRotateInterface;

class LogRotateCronjob extends CronjobAwesome
{
    public function expression(): string
    {
        return '@daily';
    }

    public function handle(): void
    {
        $logger = container('logger');
        if ($logger instanceof LogRotateInterface) {
            $logger->rotate();
        }
    }
}
