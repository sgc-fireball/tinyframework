<?php

declare(strict_types=1);

namespace TinyFramework\Logger;

interface LoggerInterface
{
    public const EMERGENCY = 'emergency';
    public const ALERT = 'alert';
    public const CRITICAL = 'critical';
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const NOTICE = 'notice';
    public const INFO = 'info';
    public const DEBUG = 'debug';

    public const LEVELS = [
        self::EMERGENCY,
        self::ALERT,
        self::CRITICAL,
        self::ERROR,
        self::WARNING,
        self::NOTICE,
        self::INFO,
        self::DEBUG
    ];

    public function emergency(string $message, array $context = []): LoggerInterface;

    public function alert(string $message, array $context = []): LoggerInterface;

    public function critical(string $message, array $context = []): LoggerInterface;

    public function error(string $message, array $context = []): LoggerInterface;

    public function warning(string $message, array $context = []): LoggerInterface;

    public function notice(string $message, array $context = []): LoggerInterface;

    public function info(string $message, array $context = []): LoggerInterface;

    public function debug(string $message, array $context = []): LoggerInterface;

    public function log(string $level, string $message, array $context = []): LoggerInterface;
}
