<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use ReflectionClass;

trait Macroable
{
    /** @var Closure[]|callable[] */
    private static array $staticMacros = [];

    /** @var Closure[]|callable[] */
    private static array $macros = [];

    public static function addStaticMacro(string $name, Closure|callable $callback): void
    {
        $reflectionClass = new ReflectionClass(static::class);
        if ($reflectionClass->hasMethod($name)) {
            $reflectionMethod = $reflectionClass->getMethod($name);
            if ($reflectionMethod->isStatic()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Could not overwrite real static method %s::%s()!',
                        static::class,
                        $name
                    )
                );
            }
        }
        static::$staticMacros[$name] = $callback;
    }

    public static function addMacro(string $name, Closure|callable $callback): void
    {
        $reflectionClass = new ReflectionClass(static::class);
        if ($reflectionClass->hasMethod($name)) {
            $reflectionMethod = $reflectionClass->getMethod($name);
            if (!$reflectionMethod->isStatic()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Could not overwrite real method %s::%s()!',
                        static::class,
                        $name
                    )
                );
            }
        }
        static::$macros[$name] = $callback;
    }

    public static function hasStaticMacro(string $name): bool
    {
        return array_key_exists($name, static::$staticMacros);
    }

    public static function hasMacro(string $name): bool
    {
        return array_key_exists($name, static::$macros);
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (static::hasStaticMacro($name)) {
            $callback = static::$staticMacros[$name];
            if ($callback instanceof Closure) {
                $callback->bindTo(null, static::class);
            }
            return $callback(...$arguments);
        }
        throw new BadMethodCallException(
            sprintf(
                'Real Method or Macromethod %s::%s() does not exists.',
                static::class,
                $name
            )
        );
    }

    public function __call(string $name, array $arguments): mixed
    {
        if (static::hasMacro($name)) {
            $callback = static::$macros[$name];
            if ($callback instanceof Closure) {
                return $callback->bindTo($this, static::class);
            }
            return $callback(...$arguments);
        }
        throw new BadMethodCallException(
            sprintf(
                'Real Method or Macromethod %s->%s() does not exists.',
                static::class,
                $name
            )
        );
    }
}
