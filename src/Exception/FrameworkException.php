<?php declare(strict_types=1);

namespace TinyFramework\Exception;

abstract class FrameworkException extends \RuntimeException implements \Throwable
{

    public function __toString(): string
    {
        return exception2text($this);
    }

}
