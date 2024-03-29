<?php

declare(strict_types=1);

namespace TinyFramework\Database;

use Closure;
use TinyFramework\Helpers\DatabaseRaw;

interface QueryInterface
{
    // @TODO with

    public function raw(string $str): DatabaseRaw;

    public function select(array $fields = []): QueryInterface;

    public function table(string $table = null): QueryInterface|string|null;

    public function class(string $class = null): QueryInterface|string|null;

    public function leftJoin(string $tableA, string $fieldA, string $tableB, string $fieldB): QueryInterface;

    public function where(
        string|DatabaseRaw|Closure $field,
        string $operation = null,
        mixed $value = null
    ): QueryInterface;

    public function whereNull(string $field): QueryInterface;

    public function whereNotNull(string $field): QueryInterface;

    public function orWhere(string|DatabaseRaw|Closure $field, string $operation, mixed $value): QueryInterface;

    public function orWhereNull(string $field, string $operation, mixed $value): QueryInterface;

    public function orWhereNotNull(string $field, string $operation, mixed $value): QueryInterface;

    public function whereNested(Closure $callback): QueryInterface;

    public function orWhereNested(Closure $callback): QueryInterface;

    public function orderBy(string $field, string $order = 'asc'): QueryInterface;

    public function groupBy(string $field): QueryInterface;

    public function limit(int $limit): QueryInterface;

    public function offset(int $offset): QueryInterface;

    public function load(): array;

    public function put(array $fields = []): array;

    public function delete(): bool;

    public function get(): array;

    public function first(): BaseModel|array|null;

    public function firstOrFail(): BaseModel;

    public function paginate(int $perPage = 20, int $page = 1): array;

    public function count(): int;

    public function transaction(): void;

    public function commit(): void;

    public function rollback(): void;

    public function with(array|string|null $paths = null): QueryInterface|array;
}
