<?php

declare(strict_types=1);

namespace TinyFramework\Tests\Core;

use DateTime;
use PHPUnit\Framework\TestCase;
use TinyFramework\Core\DotEnv;
use TinyFramework\Core\Pipeline;

class PipelineTest extends TestCase
{
    public function testCall(): void
    {
        $test = 0;
        $pipeline = new Pipeline();
        $pipeline->call(function () use (&$test) {
            $test = 1;
        });
        $this->assertEquals(1, $test);
    }

    public function testOneLayer(): void
    {
        $test = 0;
        $assertEquals = fn ($expected, $actual) => $this->assertEquals($expected, $actual);
        $assertEquals(0, $test);
        $pipeline = new Pipeline();
        $pipeline->layers(function ($parameters, $next) use (&$test, $assertEquals) {
            $assertEquals(0, $test);
            $test++;
            $assertEquals(1, $test);
            $next($parameters);
            $assertEquals(2, $test);
            $test++;
            $assertEquals(3, $test);
        });
        $pipeline->call(function () use (&$test, $assertEquals) {
            $assertEquals(1, $test);
            $test++;
            $assertEquals(2, $test);
        });
        $assertEquals(3, $test);
    }


    public function testTwoLayer(): void
    {
        $test = 0;
        $assertEquals = fn ($expected, $actual) => $this->assertEquals($expected, $actual);
        $assertEquals(0, $test);
        $pipeline = new Pipeline();
        $pipeline->layers(function ($parameters, $next) use (&$test, $assertEquals) {
            $assertEquals(0, $test);
            $test++;
            $assertEquals(1, $test);
            $next($parameters);
            $assertEquals(4, $test);
            $test++;
            $assertEquals(5, $test);
        });
        $pipeline->layers(function ($parameters, $next) use (&$test, $assertEquals) {
            $assertEquals(1, $test);
            $test++;
            $assertEquals(2, $test);
            $next($parameters);
            $assertEquals(3, $test);
            $test++;
            $assertEquals(4, $test);
        });
        $pipeline->call(function () use (&$test, $assertEquals) {
            $assertEquals(2, $test);
            $test++;
            $assertEquals(3, $test);
        });
        $assertEquals(5, $test);
    }

    public function testReturn(): void
    {
        $test = 0;
        $pipeline = new Pipeline();
        $pipeline->layers(function ($parameters, $next) use (&$test) {
            $test++;
        });
        $this->assertEquals(0, $test);
        $pipeline->call(function () {
            throw new \RuntimeException('Should never been called!');
        });
        $this->assertEquals(1, $test);
    }

    public function testParameter(): void
    {
        $check = fn ($parameter) => $this->assertEquals(1234, $parameter);
        $pipeline = new Pipeline();
        $pipeline->layers(function ($parameters, $next) use ($check) {
            $check($parameters);
            $next($parameters);
        });
        $pipeline->call(function ($parameters) use ($check) {
            $check($parameters);
        }, 1234);
    }

    public function testReturnValue(): void
    {
        $pipeline = new Pipeline();
        $pipeline->layers(function ($i, $next) {
            return $next($i + 2);
        });
        $pipeline->layers(function ($i, $next) {
            return $next($i * 2);
        });
        $pipeline->layers(function ($i, $next) {
            return $next($i + 4);
        });
        $result = $pipeline->call(function ($i) {
            return $i;
        }, 1);
        $this->assertEquals(10, $result);
    }
}
