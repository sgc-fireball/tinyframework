<?php declare(strict_types=1);

namespace TinyFramework\Database\MySQL;

use TinyFramework\Database\QueryAwesome;
use TinyFramework\Database\DatabaseInterface;

class Query extends QueryAwesome
{

    /** @var Database */
    protected DatabaseInterface $driver;

    private function compileSelect(array $fields): string
    {
        $query = trim(implode(', ', array_map(function (string $field) {
            return '`' . $field . '`';
        }, $fields)), ', ');
        return $query ?: '*';
    }

    private function compileWhere(array $wheres, bool $withWhere = false): string
    {
        $query = '';
        foreach ($wheres as $where) {
            if ($where['type'] === 'basic') {
                if ($where['value'] === null) {
                    $where['operation'] = $where['operation'] === '=' ? 'IS' : $where['operation'];
                    $where['operation'] = $where['operation'] === '!=' ? 'IS NOT' : $where['operation'];
                    $where['operation'] = $where['operation'] === '<>' ? 'IS NOT' : $where['operation'];
                }
                $query .= sprintf(
                    ' %s %s %s %s',
                    $query ? $where['boolean'] : '',
                    '`' . $where['field'] . '`',
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
        return $query ? ($withWhere ? 'WHERE ' : '') . trim($query) : '';
    }

    private function compileGroup(array $groups): string
    {
        $query = '';
        foreach ($groups as $field) {
            $query .= '`' . $field . '`,';
        }
        return $query ? 'GROUP BY ' . rtrim($query, ', ') : '';
    }

    private function compileOrder(array $orders): string
    {
        $query = '';
        foreach ($orders as $order) {
            $query .= '`' . $order['field'] . '` ' . $order['order'] . ', ';
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
        return trim(implode(', ', array_map(function ($value, $key) use ($self) {
            return sprintf('`%s` = %s', $key, $self->driver->escape($value));
        }, array_values($fields), array_keys($fields))), ' ,');
    }

    public function toSql(): string
    {
        return rtrim(sprintf(
            'SELECT %s FROM `%s` %s %s %s %s %s',
            $this->compileSelect($this->select),
            $this->table,
            $this->compileWhere($this->wheres, true),
            $this->compileGroup($this->groups),
            $this->compileOrder($this->orders),
            $this->compileLimit($this->limit),
            $this->compileOffset($this->offset)
        ));
    }

    public function load(): array
    {
        return $this->driver->execute($this->toSql());
    }

    public function put(array $fields = []): array
    {
        if (array_key_exists('id', $fields) && $fields['id']) {
            $this->driver->execute(sprintf(
                'INSERT INTO `%s` SET %s ON DUPLICATE KEY UPDATE %s',
                $this->table,
                $this->compileFieldSet($fields),
                $this->compileFieldSet(array_filter($fields, function ($value, $key) {
                    return $key !== 'id';
                }, ARRAY_FILTER_USE_BOTH))
            ));
        } else {
            $this->driver->execute(sprintf(
                'INSERT INTO `%s` SET %s',
                $this->table,
                $this->compileFieldSet(array_filter($fields, function ($value, $key) {
                    return $key !== 'id';
                }, ARRAY_FILTER_USE_BOTH))
            ));
            $fields['id'] = $this->driver->getLastInsertId();
        }
        return $fields;
    }

    public function delete(): bool
    {
        return (bool)$this->driver->execute(rtrim(sprintf(
            'DELETE FROM `%s` %s %s %s %s',
            $this->table,
            $this->compileWhere($this->wheres, true),
            $this->compileOrder($this->orders),
            $this->compileLimit($this->limit),
            $this->compileOffset($this->offset)
        )));
    }

    public function count(): int
    {
        return (int)$this->driver->execute(rtrim(sprintf(
            'SELECT COUNT(1) AS `c` FROM `%s` %s %s',
            $this->table,
            $this->compileWhere($this->wheres, true),
            $this->compileGroup($this->groups)
        )))[0]['c'];
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
