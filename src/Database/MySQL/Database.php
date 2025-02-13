<?php

declare(strict_types=1);

namespace TinyFramework\Database\MySQL;

use DateTime;
use DateTimeZone;
use mysqli;
use RuntimeException;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Helpers\DatabaseRaw;

class Database implements DatabaseInterface
{
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => null,
        'database' => null,
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_general_ci',
        'timezone' => 'UTC',
    ];

    protected ?mysqli $connection = null;

    protected array $listenCallbacks = [];

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
            $this->connection = new mysqli(
                $this->config['host'],
                $this->config['username'],
                $this->config['password'],
                $this->config['database'],
                $this->config['port']
            );
            $this->connection->query(
                sprintf(
                    "SET NAMES %s COLLATE %s",
                    $this->config['charset'],
                    $this->config['collation']
                )
            );
            $this->connection->query(
                sprintf(
                    'SET time_zone = "%s";',
                    (new DateTime('now', new DateTimeZone($this->config['timezone'])))->format('P')
                )
            );
            $this->connection->query(
                'SET SESSION sql_mode = "ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"'
            );
        } else {
            try {
                if ($this->connection->ping()) {
                    return $this;
                }
                throw new \RuntimeException('reconnect needed.');
            } catch (\Throwable $e) {
                $this->connection = null;
                return $this->connect();
            }
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

        if ($this->connection) {
            return '"' . $this->connect()->connection->real_escape_string($value) . '"';
        }

        /**
         * @link https://dev.mysql.com/doc/refman/8.0/en/string-literals.html#character-escape-sequences
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
            '\\' => '\\\\',
            /**
             * The \% and \_ sequences are used to search for literal instances of % and _ in pattern-matching contexts
             * where they would otherwise be interpreted as wildcard characters. See the description of the
             * LIKE operator in Section 14.8.1, “String Comparison Functions and Operators”.
             * If you use \% or \_ outside of pattern-matching contexts,
             * they evaluate to the strings \% and \_, not to % and _.
             */
            //'_' => '\_',
            //'%' => '\%',
        ]);
        return '"' . $value . '"';
    }

    public function query(): Query
    {
        return new Query($this);
    }

    public function execute(string $query): array|bool
    {
        $start = microtime(true);
        $result = $this->connect()->connection->query($query);
        $end = microtime(true);
        $parameter = [
            'query' => $query,
            'start' => $start,
            'duration' => $end - $start,
            'end' => $end,
        ];
        foreach ($this->listenCallbacks as $callback) {
            rescue(fn() => container()->call($callback, $parameter));
        }
        if ($result === false) {
            throw new RuntimeException(
                sprintf(
                    'Error %s: %s (%s)',
                    $this->connect()->connection->errno,
                    $this->connect()->connection->error,
                    $query
                )
            );
        }
        if (str_starts_with($query, 'SELECT')) {
            return mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return $result;
    }

    public function getLastInsertId(): int|string
    {
        return mysqli_insert_id($this->connect()->connection);
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

    public function listen(callable|string|array|\Closure $listen): static
    {
        $this->listenCallbacks[] = $listen;
        return $this;
    }

}
