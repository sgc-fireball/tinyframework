<?php declare(strict_types=1);

namespace TinyFramework\Logger;

class NullLogger extends LoggerAwesome implements LoggerInterface
{

    public function log(string $level, string $message, array $context = []): LoggerInterface
    {
        return $this;
    }

}
