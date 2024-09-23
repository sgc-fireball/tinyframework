<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Helpers;

use PHPUnit\Framework\TestCase;
use Throwable;
use TinyFramework\Helpers\Promise;

class PromiseTest extends TestCase
{

    public function testAwait(): void
    {
        $value = 'a';
        (new Promise(function () use (&$value) {
            $value = 'b';
        }))->await();
        $this->assertEquals('b', $value);
    }

    public function testMultipleAwait(): void
    {
        $value = 'a';
        (new Promise(function () use (&$value) {
            $value = 'b';
        }))->await();
        (new Promise(function () use (&$value) {
            $value = 'c';
        }))->await();
        $this->assertEquals('c', $value);
    }

    public function testPromiseAll1(): void
    {
        $value = 'a';
        Promise::all([
            (new Promise(function () use (&$value) {
                $value = 'b';
            })),
            (new Promise(function () use (&$value) {
                $value = 'c';
            })),
            (new Promise(function () use (&$value) {
                $value = 'd';
            })),
        ])->await();
        $this->assertEquals('d', $value);
    }

    public function testPromiseAll2(): void
    {
        $result = Promise::all([
            (new Promise(fn() => 1)),
            (new Promise(fn() => 2)),
            (new Promise(fn() => 3)),
            (new Promise(fn() => 4)),
            (new Promise(fn() => 5)),
        ])->await();
        $this->assertIsArray($result);
        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testPromiseAll3(): void
    {
        $result = Promise::all([
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 1;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 2;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 3;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 4;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 5;
            })),
        ])->await();
        $this->assertIsArray($result);
        $this->assertEquals([1, 2, 3, 4, 5], $result);
    }

    public function testPromiseCatchAll(): void
    {
        Promise::all([
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 1;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 2;
            })),
            (new Promise(function () {
                throw new \RuntimeException('Test Error');
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 4;
            })),
            (new Promise(function () {
                Promise::usleep(mt_rand(25_000, 75_000));
                return 5;
            })),
        ])->then(function () {
            $this->assertEquals(true, false, 'Must never be called!');
        })->catch(function (Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
            $this->assertEquals('Test Error', $e->getMessage());
        })->await();
    }

    public function testPromiseSleep1All(): void
    {
        $value = 'a';
        Promise::all([
            (new Promise(function () use (&$value) {
                $value = 'b';
            })),
            (new Promise(function () use (&$value) {
                usleep(250_000);
                $value = 'c';
            })),
            (new Promise(function () use (&$value) {
                $value = 'd';
            })),
        ])->await();
        $this->assertEquals('d', $value);
    }

    public function testPromiseSleep2All(): void
    {
        $value = 'a';
        Promise::all([
            (new Promise(function () use (&$value) {
                $value = 'b';
            })),
            (new Promise(function () use (&$value) {
                Promise::usleep(250_000);
                $value = 'c';
            })),
            (new Promise(function () use (&$value) {
                $value = 'd';
            })),
        ])->await();
        $this->assertEquals('c', $value);
    }

    public function testAllSettled(): void
    {
        $value = Promise::allSettled([
            (new Promise(function () {
                throw new \RuntimeException('Test Error');
            })),
            (new Promise(function () {
                return 'a';
            }))
        ])->await();
        $this->assertIsArray($value);
        $this->assertEquals(2, count($value));
        $this->assertArrayHasKey('status', $value[0]);
        $this->assertArrayHasKey('reason', $value[0]);
        $this->assertEquals('rejected', $value[0]['status']);
        $this->assertInstanceOf(\RuntimeException::class, $value[0]['reason']);
        $this->assertEquals('Test Error', $value[0]['reason']->getMessage());
        $this->assertArrayHasKey('status', $value[1]);
        $this->assertArrayHasKey('value', $value[1]);
        $this->assertEquals('fulfilled', $value[1]['status']);
        $this->assertEquals('a', $value[1]['value']);
    }

    // @TODO race

    public function testAny(): void
    {
        $value = Promise::any([
            (new Promise(function () {
                throw new \RuntimeException('Test Error 1');
            })),
            (new Promise(function () {
                Promise::usleep(100_000);
                return 'a';
            })),
            (new Promise(function () {
                Promise::usleep(200_000);
                return 'b';
            })),
            (new Promise(function () {
                throw new \RuntimeException('Test Error 2');
            })),
        ])->await();
        $this->assertEquals('a', $value);
    }

    public function testThen(): void
    {
        $value = null;
        (new Promise(function () {
            return 'a';
        }))->then(function (string $result) use (&$value) {
            $value = $result;
        });
        $this->assertEquals('a', $value);
    }

    public function testChainThen(): void
    {
        $value = 'a';
        $value = (new Promise(function () use ($value) {
            return $value . 'b';
        }))->then(function (string $value) {
            return $value . 'c';
        })->await();
        $this->assertEquals('abc', $value);
    }

    public function testCatch(): void
    {
        try {
            (new Promise(function () {
                throw new \RuntimeException('Test Error');
            }))->then(function (string $value) {
                return $value . 'c';
            })->catch(function (Throwable $e) {
                return $e;
            })->await();
            $this->assertEquals(true, false, 'Must never be called!');
        } catch (Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
            $this->assertEquals('Test Error', $e->getMessage());
        }
    }

    public function testCatchThen1(): void
    {
        try {
            $value = 'a';
            (new Promise(function () use ($value) {
                return $value . 'b';
            }))->then(function (string $value) {
                throw new \RuntimeException('Test Error');
            })->catch(function (Throwable $e) {
                return $e;
            })->then(function (Throwable $e) {
                return $e;
            })->await();
            $this->assertEquals(true, false, 'Must never be called!');
        } catch (Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
            $this->assertEquals('Test Error', $e->getMessage());
        }
    }

    public function testCatchThen2(): void
    {
        try {
            $value = 'a';
            $value = (new Promise(function () use ($value) {
                return $value . 'b';
            }))->then(function () {
                throw new \RuntimeException('Test Error');
            })->catch(function (Throwable $e) {
                return 'c';
            })->then(function (string $value) {
                return $value . 'd';
            })->await();
            $this->assertEquals('cd', $value);
        } catch (Throwable $e) {
            $this->assertEquals(true, false, 'Must never be called!');
        }
    }

}
