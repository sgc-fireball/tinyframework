<?php declare(strict_types=1);

namespace TinyFramework\Database;

use Closure;

interface QueryInterface
{

    public function select(array $fields = []);

    public function table(string $table);

    public function class(string $class);

    /**
     * @param string|Closure $field
     * @param string|null $operation
     * @param mixed $value
     * @return QueryInterface
     */
    public function where($field, string $operation = null, $value = null);

    /**
     * @param string|Closure $field
     * @param string $operation
     * @param mixed $value
     * @return QueryInterface
     */
    public function orWhere($field, string $operation, $value);

    public function whereNested(Closure $callback);

    public function orWhereNested(Closure $callback);

    public function orderBy(string $field, string $order = 'asc');

    public function groupBy(string $field);

    public function limit(int $limit);

    public function offset(int $offset);

    public function load(): array;

    public function put(array &$fields = []);

    public function delete();

    public function get(): array;

    public function first(): ?BaseModel;

    public function count(): int;

}
