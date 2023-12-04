<?php

declare(strict_types=1);

namespace TinyFramework\Database;

interface DatabaseInterface
{
    public function __construct(#[\SensitiveParameter] array $config = []);

    public function connect(): DatabaseInterface;

    public function reconnect(): DatabaseInterface;

    public function disconnect(): DatabaseInterface;

    public function escape(mixed $value): string|float|int;

    public function query(): QueryInterface;

    public function execute(string $query): array|bool;

    public function createMigrationTable(): DatabaseInterface;
}
