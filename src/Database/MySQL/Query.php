<?php

declare(strict_types=1);

namespace TinyFramework\Database\MySQL;

use Closure;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\QueryAwesome;
use TinyFramework\Helpers\DatabaseRaw;

class Query extends QueryAwesome
{
    /** @var Database */
    protected DatabaseInterface $driver;

    protected ?string $title = null;

    protected array $havings = [];

    public function title(?string $title = null): string|null|static
    {
        if ($title === null) {
            return $this->title;
        }
        $this->title = str_replace(["\r", "\n"], ' - ', $title);
        return $this;
    }

    public function having(
        string|DatabaseRaw|Closure $field,
        string $operation = null,
        mixed $value = null
    ): Query {
        if ($field instanceof Closure) {
            return $this->havingNested($field);
        }
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => $operation,
            'value' => $value,
        ];
        return $this;
    }

    public function havingNull(string $field): static
    {
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => '=',
            'value' => null,
        ];
        return $this;
    }

    public function havingNotNull(string $field): static
    {
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => '!=',
            'value' => null,
        ];
        return $this;
    }

    public function orHaving(string|DatabaseRaw|Closure $field, string $operation, mixed $value): static
    {
        if ($field instanceof Closure) {
            return $this->havingNested($field);
        }
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => $operation,
            'value' => $value,
        ];
        return $this;
    }

    public function orHavingNull(string $field, string $operation, mixed $value): static
    {
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => '!=',
            'value' => null,
        ];
        return $this;
    }

    public function orHavingNotNull(string $field, string $operation, mixed $value): static
    {
        $this->havings[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => '!=',
            'value' => null,
        ];
        return $this;
    }

    public function havingNested(Closure $callback): static
    {
        $class = \get_class($this);
        \call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (\count($query->havings)) {
            $this->havings[] = [
                'type' => 'nested',
                'boolean' => 'and',
                'query' => $query,
            ];
        }
        return $this;
    }

    public function orHavingNested(Closure $callback): static
    {
        $class = \get_class($this);
        \call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (\count($query->havings)) {
            $this->havings[] = [
                'type' => 'nested',
                'boolean' => 'or',
                'query' => $query,
            ];
        }
        return $this;
    }

    private function compileSelect(array $fields): string
    {
        $query = trim(
            implode(
                ', ',
                array_map(function (DatabaseRaw|string $field): string {
                    if ($field instanceof DatabaseRaw) {
                        return $field->__toString();
                    }
                    return '`' . $this->table . '`.`' . $field . '`';
                }, $fields)
            ),
            ', '
        );
        return $query ?: '`' . $this->table . '`.*';
    }

    private function compileJoins(array $joins): string
    {
        $query = '';
        foreach ($joins as $join) {
            $query .= vnsprintf(
                ' {type} JOIN `{tableB}` ON (`{tableB}`.`{fieldB}` = `{tableA}`.`{fieldA}`)',
                array_merge($join, ['type' => strtoupper($join['type'] ?? 'INNER')])
            );
        }
        return trim($query);
    }

    private function compileWhere(array $wheres, string $with = null): string
    {
        $query = '';
        foreach ($wheres as $where) {
            if ($where['type'] === 'basic') {
                if ($where['value'] === null) {
                    $where['operation'] = $where['operation'] === '=' ? 'IS' : $where['operation'];
                    $where['operation'] = $where['operation'] === '!=' ? 'IS NOT' : $where['operation'];
                    $where['operation'] = $where['operation'] === '<>' ? 'IS NOT' : $where['operation'];
                }
                $field = $where['field'] instanceof DatabaseRaw
                    ? $where['field']->__toString()
                    : '`' . str_replace('`', '', $where['field']) . '`';
                $query .= sprintf(
                    ' %s %s %s %s',
                    $query ? $where['boolean'] : '',
                    $field,
                    $where['operation'],
                    $this->driver->escape($where['value'])
                );
            } elseif ($where['type'] === 'nested') {
                $query .= sprintf(
                    ' %s (%s)',
                    $query ? $where['boolean'] : '',
                    $this->compileWhere($where['query']->wheres)
                );
            }
        }
        return $query ? ($with ? $with . ' ' : '') . trim($query) : '';
    }

    private function compileGroup(array $groups): string
    {
        $query = '';
        foreach ($groups as $field) {
            $field = $field instanceof DatabaseRaw
                ? $field->__toString()
                : '`' . str_replace('`', '', $field) . '`';
            $query .= $field . ', ';
        }
        return $query ? 'GROUP BY ' . rtrim($query, ', ') : '';
    }

    private function compileOrder(array $orders): string
    {
        $query = '';
        foreach ($orders as $order) {
            $order['field'] = $order['field'] instanceof DatabaseRaw
                ? $order['field']->__toString()
                : '`' . str_replace('`', '', $order['field']) . '`';
            $query .= $order['field'] . ' ' . $order['order'] . ', ';
        }
        return $query ? 'ORDER BY ' . rtrim($query, ', ') : '';
    }

    private function compileLimit(int $limit = null): string
    {
        return $limit ? 'LIMIT ' . $limit : '';
    }

    private function compileOffset(int $offset = null): string
    {
        return $offset ? 'LIMIT ' . $offset : '';
    }

    private function compileFieldSet(array $fields = []): string
    {
        $self = $this;
        return trim(
            implode(
                ', ',
                array_map(function ($value, $key) use ($self) {
                    return sprintf(
                        '`%s` = %s',
                        str_replace('`', '', $key),
                        $self->driver->escape($value)
                    );
                }, array_values($fields), array_keys($fields))
            ),
            ' ,'
        );
    }

    public function toSql(): string
    {
        $sql = rtrim(
            vnsprintf(
                'SELECT {field} FROM {table} {join} {where} {group} {having} {order} {limit} {offset}',
                [
                    'field' => $this->compileSelect($this->select),
                    'table' => $this->table,
                    'join' => $this->compileJoins($this->joins),
                    'where' => $this->compileWhere($this->wheres, 'WHERE'),
                    'group' => $this->compileGroup($this->groups),
                    'having' => $this->compileWhere($this->havings, 'HAVING'),
                    'order' => $this->compileOrder($this->orders),
                    'limit' => $this->compileLimit($this->limit),
                    'offset' => $this->compileOffset($this->offset),
                ]
            )
        );
        if ($this->title) {
            $sql = "# " . $this->title . "\n" . $sql;
        }
        return $sql;
    }

    public function load(): array
    {
        return $this->driver->execute($this->toSql());
    }

    public function put(array $fields = []): array
    {
        if (array_key_exists('id', $fields) && $fields['id']) {
            $query = vnsprintf(
                'INSERT INTO `{table}` SET {fields1} ON DUPLICATE KEY UPDATE {fields2}',
                [
                    'table' => $this->table,
                    'fields1' => $this->compileFieldSet($fields),
                    'fields2' => $this->compileFieldSet(
                        array_filter($fields, function ($value, $key) {
                            return $key !== 'id';
                        }, ARRAY_FILTER_USE_BOTH)
                    ),
                ]
            );
            $this->driver->execute($query);
        } else {
            $query = vnsprintf(
                'INSERT INTO `{table}` SET {fields}',
                [
                    'table' => $this->table,
                    'fields' => $this->compileFieldSet($fields),
                ]
            );
            $this->driver->execute($query);
            $fields['id'] = $this->driver->getLastInsertId();
        }
        return $fields;
    }

    public function delete(): bool
    {
        $query = rtrim(
            vnsprintf(
                'DELETE FROM {table} {where} {order} {limit} {offset}',
                [
                    'table' => $this->table,
                    'where' => $this->compileWhere($this->wheres, 'WHERE'),
                    'order' => $this->compileOrder($this->orders),
                    'limit' => $this->compileLimit($this->limit),
                    'offset' => $this->compileOffset($this->offset),
                ]
            )
        );
        return (bool)$this->driver->execute($query);
    }

    public function count(): int
    {
        $query = (clone $this)->select([new DatabaseRaw('COUNT(1) AS `count`')])->toSql();
        return (int)$this->driver->execute($query)[0]['count'];
    }

    public function sum(string $field): float
    {
        $field = str_replace('`', '', $field);
        $query = (clone $this)->select([new DatabaseRaw('SUM(`' . $field . '`) AS `sum`')])->toSql();
        return (float)$this->driver->execute($query)[0]['sum'];
    }

    public function searchTerm(string $term, array $fields = []): static
    {
        if (!count($fields) || empty($term)) {
            return $this;
        }
        $minValue = PHP_INT_MAX;
        $ifs = array_map(function (float $value, string $field) use ($term, &$minValue) {
            $likeValue = ceil($value / 2);
            $minValue = max(1, min($minValue, $likeValue));
            return sprintf(
                'IF(%s = %s, %d, 0) + IF(%s LIKE %s, %d, 0)',
                '`' . $this->table . '`.`' . $field . '`',
                $this->driver->escape($term),
                ceil($value),
                '`' . $this->table . '`.`' . $field . '`',
                $this->driver->escape('%' . str_replace('%', '%%', $term) . '%'),
                $likeValue
            );
        }, array_values($fields), array_keys($fields));
        $raw = 'SUM(' . implode(' + ', $ifs) . ') as `_score`';
        $this->select([new DatabaseRaw($raw)]);
        $this->where('_score', '>=', $minValue);
        $this->orderBy('_score', 'DESC');
        return $this;
    }

    public function avg(string $field): float
    {
        $field = str_replace('`', '', $field);
        $query = (clone $this)->select([new DatabaseRaw('AVG(`' . $field . '`) AS `avg`')])->toSql();
        return (float)$this->driver->execute($query)[0]['avg'];
    }

    public function min(string $field): float
    {
        $field = str_replace('`', '', $field);
        $query = (clone $this)->select([new DatabaseRaw('MIN(`' . $field . '`) AS `min`')])->toSql();
        return (float)$this->driver->execute($query)[0]['min'];
    }

    public function max(string $field): float
    {
        $field = str_replace('`', '', $field);
        $query = (clone $this)->select([new DatabaseRaw('MAX(`' . $field . '`) AS `max`')])->toSql();
        return (float)$this->driver->execute($query)[0]['max'];
    }

    public function transaction(): void
    {
        $this->driver->execute('START TRANSACTION');
    }

    public function commit(): void
    {
        $this->driver->execute('COMMIT');
    }

    public function rollback(): void
    {
        $this->driver->execute('ROLLBACK');
    }
}
