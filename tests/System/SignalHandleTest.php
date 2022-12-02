<?php

declare(strict_types=1);

namespace TinyFramework\Tests\System;

use PHPUnit\Framework\TestCase;
use TinyFramework\Event\EventDispatcher;
use TinyFramework\System\SignalEvent;
use TinyFramework\System\SignalHandler;

class SignalHandleTest extends TestCase
{
    public function testSigHup(): void
    {
        if (!function_exists('posix_kill') || !function_exists('posix_getpid')) {
            $this->markTestSkipped('Missing posix extension.');
        }

        $called = false;
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(SignalEvent::class, function (SignalEvent $event) use (&$called) {
            $called = true;
        });

        (new \ReflectionClass(SignalHandler::class))->getProperty('eventDispatcher')->setValue($eventDispatcher);
        pcntl_async_signals(true);
        SignalHandler::catchSignal(SignalHandler::SIGHUP);
        $this->assertFalse($called);
        posix_kill(posix_getpid(), SignalHandler::SIGHUP);
        $this->assertTrue($called);
    }

    public function testSigTerm(): void
    {
        if (!function_exists('posix_kill') || !function_exists('posix_getpid')) {
            $this->markTestSkipped('Missing posix extension.');
        }

        $eventDispatcher = new EventDispatcher();
        (new \ReflectionClass(SignalHandler::class))->getProperty('eventDispatcher')->setValue($eventDispatcher);
        pcntl_async_signals(true);
        SignalHandler::catchSignal(SignalHandler::SIGTERM);
        $this->assertFalse(SignalHandler::isTerminated());
        posix_kill(posix_getpid(), SignalHandler::SIGTERM);
        $this->assertTrue(SignalHandler::isTerminated());
    }
}
