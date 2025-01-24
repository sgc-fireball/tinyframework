<?php

declare(strict_types=1);

namespace TinyFramework\Cron;

/**
 * @deprecated Please use CronjobAwesome instead!
 */
interface CronjobInterface
{
    public function expression(): string;

    public function handle(): void;
}
