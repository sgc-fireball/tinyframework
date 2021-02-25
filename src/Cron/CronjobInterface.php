<?php declare(strict_types=1);

namespace TinyFramework\Cron;

interface CronjobInterface
{

    public function expression(): string;

    public function handle(): void;

}
