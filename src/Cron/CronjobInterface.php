<?php

declare(strict_types=1);

namespace TinyFramework\Cron;

interface CronjobInterface
{
    public function expression(): string;

    public function handle(): void;

    // @TODO onOneServer
    // @TODO withoutOverlapping
    // @TODO onFailure(): callback
    // @TODO onSuccess(): callback
}
