<?php

declare(strict_types=1);

namespace TinyFramework\System;

use TinyFramework\Event\EventDispatcherInterface;

class SignalHandler
{
    public const SIGNULL = 0;
    public const SIGHUP = 1;
    public const SIGINT = 2;
    public const SIGQUIT = 3;
    public const SIGILL = 4;
    public const SIGTRAP = 5;
    public const SIGABRT = 6;
    public const SIGBUS = 7;
    public const SIGFPE = 8;
    public const SIGKILL = 9;
    public const SIGUSR1 = 10;
    public const SIGSEGV = 11;
    public const SIGUSR2 = 12;
    public const SIGPIPE = 13;
    public const SIGALRM = 14;
    public const SIGTERM = 15;
    public const SIGSTKFLT = 16;
    public const SIGCHLD = 17;
    public const SIGCONT = 18;
    public const SIGSTOP = 19;
    public const SIGTSTP = 20;
    public const SIGTTIN = 21;
    public const SIGTTOU = 22;
    public const SIGURG = 23;
    public const SIGXCPU = 24;
    public const SIGXFSZ = 25;
    public const SIGVTALRM = 26;
    public const SIGPROF = 27;
    public const SIGWINCH = 28;
    public const SIGIO = 29;
    public const SIGPWR = 30;
    public const SIGUNUSED = 31;

    public const NAMES = [
        0 => 'SIGNULL',
        1 => 'SIGHUP',
        2 => 'SIGINT',
        3 => 'SIGQUIT',
        4 => 'SIGILL',
        5 => 'SIGTRAP',
        6 => 'SIGABRT',
        7 => 'SIGBUS',
        8 => 'SIGFPE',
        9 => 'SIGKILL',
        10 => 'SIGUSR1',
        11 => 'SIGSEGV',
        12 => 'SIGUSR2',
        13 => 'SIGPIPE',
        14 => 'SIGALRM',
        15 => 'SIGTERM',
        16 => 'SIGSTKFLT',
        17 => 'SIGCHLD',
        18 => 'SIGCONT',
        20 => 'SIGTSTP',
        19 => 'SIGSTOP',
        21 => 'SIGTTIN',
        22 => 'SIGTTOU',
        23 => 'SIGURG',
        24 => 'SIGXCPU',
        25 => 'SIGXFSZ',
        26 => 'SIGVTALRM',
        27 => 'SIGPROF',
        28 => 'SIGWINCH',
        29 => 'SIGIO',
        30 => 'SIGPWR',
        31 => 'SIGUNUSED',
    ];

    private static bool $isTerminated = false;

    private static ?EventDispatcherInterface $eventDispatcher = null;

    public static function init(EventDispatcherInterface $eventDispatcher): void
    {
        if (self::$eventDispatcher !== null) {
            throw new \RuntimeException('Only one call of SignalHandler::init is allowed.');
        }
        pcntl_async_signals(true);
        self::$eventDispatcher = $eventDispatcher;
    }

    public static function catchAll(): void
    {
        foreach (static::NAMES as $signal => $name) {
            static::catchSignal($signal);
        }
    }

    public static function catchSignal(int $signal): bool
    {
        if ($signal >= 1 && !in_array($signal, [self::SIGKILL, self::SIGSTOP])) {
            return pcntl_signal($signal, [__CLASS__, 'signal']);
        }
        return false;
    }

    /**
     * @param int $signal
     * @param array $info
     * @return bool
     * @internal
     */
    public static function signal(int $signal, array $info = []): bool
    {
        $event = new SignalEvent($signal, self::NAMES[$signal], $info);
        if (self::$eventDispatcher) {
            self::$eventDispatcher->dispatch($event);
        }
        if (!self::$isTerminated) {
            self::$isTerminated = \in_array($signal, [self::SIGINT, self::SIGTERM]);
        }
        if ($event->isPropagationStopped()) {
            return false;
        }
        if (self::$isTerminated) {
            return true;
        }
        return false;
    }

    public static function isTerminated(): bool
    {
        return self::$isTerminated;
    }
}
