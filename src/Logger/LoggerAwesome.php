<?php declare(strict_types=1);

namespace TinyFramework\Logger;

use InvalidArgumentException;

abstract class LoggerAwesome implements LoggerInterface
{

    public function emergency(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::EMERGENCY, $message, $context);
    }

    public function alert(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::ALERT, $message, $context);
    }

    public function critical(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::CRITICAL, $message, $context);
    }

    public function error(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::ERROR, $message, $context);
    }

    public function warning(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::WARNING, $message, $context);
    }

    public function notice(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::NOTICE, $message, $context);
    }

    public function info(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::INFO, $message, $context);
    }

    public function debug(string $message, array $context = []): LoggerInterface
    {
        return $this->log(LoggerInterface::DEBUG, $message, $context);
    }

    protected function buildMessage(string $level, string $message, array $context = []): string
    {
        if (!in_array($level, LoggerInterface::LEVELS)) {
            throw new InvalidArgumentException('Invalid level.');
        }
        foreach ($context as $key => $value) {
            $value = \is_object($value) && method_exists($value, '__toString') ? $value->__toString() : $value;
            $value = \is_bool($value) ? ($value ? 'TRUE' : 'FALSE') : $value;
            $value = $value === null ? 'NULL' : $value;
            $context[$key] = (string)$value;
        }
        return vnsprintf($message, $context);
    }

}
