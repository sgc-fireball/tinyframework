<?php declare(strict_types=1);

namespace TinyFramework\Database\MySQL;

use mysqli;
use TinyFramework\Database\DatabaseInterface;

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

    public function __construct(array $config = [])
    {
        foreach ($this->config as $key => $default) {
            if (array_key_exists($key, $config)) {
                $this->config[$key] = $config[$key];
            }
        }
    }

    /**
     * @return static
     */
    public function connect(): static
    {
        if (!$this->connection) {
            $this->connection = new mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database'], $this->config['port']);
            $this->connection->query(sprintf("SET NAMES %s COLLATE %s", $this->config['charset'], $this->config['collation']));
            $this->connection->query(sprintf('SET time_zone = "%s";', (new \DateTime('now', new \DateTimeZone($this->config['timezone'])))->format('P')));
            $this->connection->query('SET SESSION sql_mode = "STRICT_TRANS_TABLES"');
        }
        return $this;
    }

    /**
     * @return static
     */
    public function reconnect(): static
    {
        return $this->disconnect()->connect();
    }

    /**
     * @return static
     */
    public function disconnect(): static
    {
        if ($this->connection) {
            $this->connection->close();
        }
        $this->connection = null;
        return $this;
    }

    public function escape($value): string|float|int
    {
        if (is_array($value)) {
            return sprintf('(%s)', implode(',', array_map(function ($value) {
                return $this->escape($value);
            }, array_values($value))));
        } elseif (is_null($value)) {
            return 'NULL';
        } elseif (is_float($value) || is_int($value)) {
            return $value;
        } elseif (is_bool($value)) {
            return $value === true ? 'TRUE' : 'FALSE';
        } elseif (is_object($value)) {
            if (method_exists($value, 'toString')) return $this->escape($value->toString());
            if (method_exists($value, '__toString')) return $this->escape($value->__toString());
            if (method_exists($value, 'toArray')) return $this->escape($value->toArray());
            if (method_exists($value, '__toArray')) return $this->escape($value->__toArray());
            return $this->escape(serialize($value));
        }
        return '"' . $this->connect()->connection->real_escape_string($value) . '"';
    }

    public function query(): Query
    {
        return new Query($this);
    }

    public function execute(string $query): array|bool
    {
        $result = $this->connect()->connection->query($query);
        if ($result === false) {
            throw new \RuntimeException(
                sprintf('Error %s: %s',
                    $this->connect()->connection->errno,
                    $this->connect()->connection->error
                )
            );
        }
        if (mb_strpos($query, 'SELECT') === 0) {
            return (array)mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        return $result;
    }

    public function getLastInsertId(): int|string
    {
        return mysqli_insert_id($this->connect()->connection);
    }

    public function createMigrationTable(): static
    {
        $this->connect()->execute(implode(" ", [
            'CREATE TABLE IF NOT EXISTS `migrations` (',
            '`id` varchar(255) NOT NULL,',
            '`batch` int(11) unsigned NOT NULL,',
            'PRIMARY KEY (`migration`)',
            ')',
        ]));
        return $this;
    }

}
