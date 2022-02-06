<?php

declare(strict_types=1);

namespace TinyFramework\Tests\System;

use PHPUnit\Framework\TestCase;
use TinyFramework\System\SignalEvent;

class SignalEventTest extends TestCase
{
    public function testEvent(): void
    {
        $event = new SignalEvent(
            $signal = time(),
            $name = 'name' . time(),
            $info = ['name' => $signal]
        );
        $this->assertEquals($signal, $event->signal());
        $this->assertEquals($name, $event->name());
        $this->assertEquals($info, $event->info());
    }
}
