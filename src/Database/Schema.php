<?php

namespace TinyFramework\Database;

/**
 * @TODO
 */
class Schema
{
    protected DatabaseInterface $connection;

    public static function connection(string $connection = null): self
    {
        return new Schema($connection);
    }

    public function __construct(string $connection = null)
    {
        $connection ??= config('database.default');
        $this->connection = container('database.' . $connection);
    }

    public function createDatabase(string $database, \Closure $closure): static
    {
        return $this;
    }

    public function updateDatabase(string $database, \Closure $closure): static
    {
        return $this;
    }

    public function dropDatabase(string $database): static
    {
        return $this;
    }

    public function createTable(string $table, \Closure $closure): static
    {
        return $this;
    }

    public function updateTable(string $database, \Closure $closure): static
    {
        return $this;
    }

    public function dropTable(string $table): static
    {
        return $this;
    }
}
