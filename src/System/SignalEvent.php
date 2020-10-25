<?php declare(strict_types=1);

namespace TinyFramework\System;

use TinyFramework\Event\EventInterface;
use TinyFramework\Event\EventAwesome;

class SignalEvent extends EventAwesome implements EventInterface
{

    private int $signal;
    private string $name;
    private array $info;

    public function __construct(int $signal, string $name, array $info = [])
    {
        $this->signal = $signal;
        $this->name = $name;
        $this->info = $info;
    }

    public function signal(): int
    {
        return $this->signal;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function info(): array
    {
        return $this->info;
    }

}
