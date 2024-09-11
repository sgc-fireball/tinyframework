<?php

declare(strict_types=1);

namespace TinyFramework\Database\SQLite;

use DateTime;
use DateTimeZone;
use SQLite3;
use RuntimeException;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Helpers\DatabaseRaw;

class Database implements DatabaseInterface
{
    protected array $config = [
        'file' => 'database.sqlite3',
        'flags' => 2 /*SQLITE3_OPEN_READWRITE*/ | 4 /* SQLITE3_OPEN_CREATE */,
        'timezone' => 'UTC',
        'encryption' => '',
    ];

    protected ?SQLite3 $connection = null;

    public function __construct(#[\SensitiveParameter] array $config = [])
    {
        foreach ($this->config as $key => $default) {
            if (array_key_exists($key, $config)) {
                $this->config[$key] = $config[$key];
            }
        }
    }

    public function connect(): static
    {
        if (!$this->connection) {
            $this->connection = new SQLite3(
                $this->config['file'],
                $this->config['flags'],
                (string)$this->config['encryption']
            );
            $this->connection->query('PRAGMA journal_mode = WAL');
            $this->connection->query('PRAGMA synchronous = NORMAL');
            $this->connection->query('PRAGMA journal_size_limit = 6144000');
        }
        return $this;
    }

    public function reconnect(): static
    {
        return $this->disconnect()->connect();
    }

    /**
     * @return static
     */
    public function disconnect(): static
    {
        $this->connection?->close();
        $this->connection = null;
        return $this;
    }

    public function escape(mixed $value): string|float|int
    {
        if ($value instanceof DatabaseRaw) {
            return $value->__toString();
        }
        if (is_array($value)) {
            return sprintf(
                '(%s)',
                implode(
                    ',',
                    array_map(function ($value) {
                        return $this->escape($value);
                    }, array_values($value))
                )
            );
        } elseif ($value === null) {
            return 'NULL';
        } elseif (is_float($value) || is_int($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        } elseif ($value instanceof DateTime) {
            $value->setTimezone(new DateTimeZone($this->config['timezone']));
            return $this->escape($value->format('Y-m-d H:i:s.u'));
        } elseif (is_object($value)) {
            if (method_exists($value, 'toString')) {
                return $this->escape($value->toString());
            } elseif (method_exists($value, '__toString')) {
                return $this->escape($value->__toString());
            } elseif (method_exists($value, 'toArray')) {
                return $this->escape($value->toArray());
            } elseif (method_exists($value, '__toArray')) {
                return $this->escape($value->__toArray());
            } elseif (method_exists($value, 'jsonSerializable')) {
                return $this->escape(json_encode($value));
            } else {
                return $this->escape(serialize($value));
            }
        }

        /**
         * @link https://github.com/abreksa4/sqlite-escape-string-polyfill/blob/master/src/functions.php
         * @link https://dev.sqlite.com/doc/refman/8.0/en/string-literals.html#character-escape-sequences
         */
        $value = strtr($value, [
            "\0" => "\\0",
            "\n" => "\\n",
            "\r" => "\\r",
            "\t" => "\\t",
            chr(26) => "\\Z",
            chr(8) => "\\b",
            '"' => '\"',
            "'" => "\'",
            '_' => '\_',
            '%' => '\%',
            '\\' => '\\\\',
        ]);
        return '"' . $value . '"';
    }

    public function query(): Query
    {
        return new Query($this);
    }

    public function execute(string $query): array|bool
    {
        $result = $this->connect()->connection->query($query);
        if ($result === false) {
            throw new RuntimeException(
                sprintf(
                    'Error %s: %s (%s)',
                    $this->connect()->connection->lastErrorCode(),
                    $this->connect()->connection->lastErrorMsg(),
                    $query
                )
            );
        }
        if (str_starts_with($query, 'SELECT')) {
            $list = [];
            while ($item = $result->fetchArray(SQLITE3_ASSOC)) {
                $list[] = $item;
            }
            return $list;
        }
        return true;
    }

    public function getLastInsertId(): int
    {
        return $this->connect()->connection->lastInsertRowID();
    }

    public function createMigrationTable(): static
    {
        $this->connect()->execute(
            implode(" ", [
                'CREATE TABLE IF NOT EXISTS `migrations` (',
                '`id` varchar(255) NOT NULL,',
                '`batch` int(11) unsigned NOT NULL,',
                'PRIMARY KEY (`id`)',
                ')',
            ])
        );
        return $this;
    }
}
