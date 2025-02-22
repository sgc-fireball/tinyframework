<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use Fiber;
use Throwable;
use TinyFramework\Exception\PromiseException;

class Promise
{

    private Fiber $fiber;

    private static function wrap(mixed $result): Promise
    {
        return $result instanceof Promise ?
            $result :
            new Promise(fn() => $result);
    }

    /**
     * @param mixed $value
     * @return Promise
     */
    public static function resolve(mixed $value = null): Promise
    {
        return new Promise(fn() => $value);
    }

    /**
     * @param Throwable $value
     * @return Promise
     */
    public static function reject(Throwable $value = null): Promise
    {
        return new Promise(fn() => ($value ?? new PromiseException('Promise rejected.')));
    }

    /**
     * @param int $sleep
     * @return void
     * @throws Throwable
     */
    public static function sleep(int $sleep): void
    {
        if (Fiber::getCurrent()) {
            $start = microtime(true);
            do {
                Fiber::suspend();
                $end = microtime(true);
                $duration = $end - $start;
            } while ($duration < $sleep);
        } else {
            sleep($sleep);
        }
    }

    /**
     * @param int $usleep
     * @return void
     * @throws Throwable
     */
    public static function usleep(int $usleep): void
    {
        if (Fiber::getCurrent()) {
            $start = microtime(true) * 1000000;
            do {
                Fiber::suspend();
                $end = microtime(true) * 1000000;
                $duration = $end - $start;
            } while ($duration < $usleep);
        } else {
            usleep($usleep);
        }
    }

    /**
     * @param Promise[] $promises
     * @return Promise
     */
    public static function all(array $promises): Promise
    {
        $result = [];
        $count = count($promises);
        while ($count != count($result)) {
            foreach ($promises as $index => $promise) {
                if ($promise->fiber->isSuspended()) {
                    $promise->fiber->resume();
                    continue;
                }
                if (!$promise->fiber->isTerminated()) {
                    continue;
                }
                $result[$index] = $promise->fiber->getReturn();
                if ($result[$index] instanceof Throwable) {
                    return Promise::reject($result[$index]);
                }
            }
        }
        return Promise::wrap($result);
    }

    /**
     * @param Promise[] $promises
     * @return Promise
     */
    public static function allSettled(array $promises): Promise
    {
        $result = [];
        $count = count($promises);
        while ($count != count($result)) {
            foreach ($promises as $index => $promise) {
                if ($promise->fiber->isSuspended()) {
                    $promise->fiber->resume();
                    continue;
                }
                if (!$promise->fiber->isTerminated()) {
                    continue;
                }
                $promiseResult = $promise->fiber->getReturn();
                $status = $promiseResult instanceof Throwable ? 'rejected' : 'fulfilled';
                $result[$index] = [
                    'status' => $promiseResult instanceof Throwable ? 'rejected' : 'fulfilled',
                    ($status === 'rejected' ? 'reason' : 'value') => $promiseResult,
                ];
            }
        }
        return Promise::wrap($result);
    }

    /**
     * @param Promise[] $promises
     * @return Promise
     */
    public static function race(array $promises): Promise
    {
        $result = [];
        $count = count($promises);
        while ($count != count($result)) {
            foreach ($promises as $index => $promise) {
                if ($promise->fiber->isSuspended()) {
                    $promise->fiber->resume();
                    continue;
                }
                if (!$promise->fiber->isTerminated()) {
                    continue;
                }
                $result[$index] = $promise->fiber->getReturn();
                if ($result[$index] instanceof Throwable) {
                    return Promise::reject($result[$index]);
                }
                return Promise::resolve($result[$index]);
            }
        }
        return Promise::reject(new PromiseException('This exception must never be called, only for IDE and Linters'));
    }

    public static function any(array $promises): Promise
    {
        $result = [];
        $count = count($promises);
        while ($count != count($result)) {
            foreach ($promises as $index => $promise) {
                if ($promise->fiber->isSuspended()) {
                    $promise->fiber->resume();
                    continue;
                }
                if (!$promise->fiber->isTerminated()) {
                    continue;
                }
                $result[$index] = $promise->fiber->getReturn();
                if (!($result[$index] instanceof Throwable)) {
                    return Promise::resolve($result[$index]);
                }
            }
        }
        return Promise::reject(new PromiseException('All promises were rejected'));
    }

    public function __construct(callable $callback)
    {
        $this->fiber = new Fiber(function () use ($callback): mixed {
            try {
                return $callback();
            } catch (Throwable $e) {
                return $e;
            }
        });
        $this->fiber->start();
    }

    public function then(callable $resolve, callable|null $reject = null): Promise
    {
        while ($this->fiber->isSuspended()) {
            $this->fiber->resume();
        }
        $result = $this->fiber->getReturn() ?? null;
        if ($result instanceof Throwable) {
            if ($reject) {
                try {
                    return Promise::wrap($reject($result));
                } catch (\Throwable $e) {
                    return Promise::reject($e);
                }
            }
            return Promise::reject($result);
        }
        try {
            return Promise::wrap($resolve($result));
        } catch (Throwable $e) {
            return Promise::reject($e);
        }
    }

    /**
     * @return mixed
     * @throws Throwable
     */
    public function await(): mixed
    {
        while ($this->fiber->isSuspended()) {
            $this->fiber->resume();
        }
        $result = $this->fiber->getReturn() ?? null;
        if ($result instanceof Throwable) {
            throw $result;
        }
        return $result;
    }

    public function catch(callable $reject): Promise
    {
        return $this->then(function (): void {
        }, $reject);
    }

}
