<?php declare(strict_types=1);

namespace TinyFramework\Database;

interface DatabaseInterface
{

    public function __construct(array $config = []);

    public function connect(): DatabaseInterface;

    public function reconnect(): DatabaseInterface;

    public function disconnect(): DatabaseInterface;

    public function escape($value): string|float|int;

    public function query(): QueryInterface;

    public function execute(string $query): array|bool;

}
