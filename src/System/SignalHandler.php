<?php declare(strict_types=1);

namespace TinyFramework\System;

use TinyFramework\Event\EventDispatcherInterface;

class SignalHandler
{

    const SIGNULL = 0;
    const SIGHUP = 1;
    const SIGINT = 2;
    const SIGQUIT = 3;
    const SIGILL = 4;
    const SIGTRAP = 5;
    const SIGABRT = 6;
    const SIGBUS = 7;
    const SIGFPE = 8;
    const SIGKILL = 9;
    const SIGUSR1 = 10;
    const SIGSEGV = 11;
    const SIGUSR2 = 12;
    const SIGPIPE = 13;
    const SIGALRM = 14;
    const SIGTERM = 15;
    const SIGSTKFLT = 16;
    const SIGCHLD = 17;
    const SIGCONT = 18;
    const SIGSTOP = 19;
    const SIGTSTP = 20;
    const SIGTTIN = 21;
    const SIGTTOU = 22;
    const SIGURG = 23;
    const SIGXCPU = 24;
    const SIGXFSZ = 25;
    const SIGVTALRM = 26;
    const SIGPROF = 27;
    const SIGWINCH = 28;
    const SIGIO = 29;
    const SIGPWR = 30;
    const SIGUNUSED = 31;

    const NAMES = [
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

    private static EventDispatcherInterface $eventDispatcher;

    public static function init(EventDispatcherInterface $eventDispatcher): void
    {
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
        self::$eventDispatcher->dispatch($event);
        if ($event->isPropagationStopped()) {
            return false;
        }
        if (!self::$isTerminated) {
            self::$isTerminated = in_array($signal, [self::SIGINT, self::SIGTERM]);
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
