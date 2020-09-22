<?php declare(strict_types=1);

namespace TinyFramework\Database\MySQL;

use mysqli;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\QueryInterface;
use TinyFramework\Queue\QueueInterface;

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
     * @return $this
     */
    public function connect(): DatabaseInterface
    {
        if (!$this->connection) {
            $this->connection = new mysqli($this->config['host'], $this->config['username'], $this->config['password'], $this->config['database'], $this->config['port']);
            $this->connection->query(sprintf("SET NAMES %s COLLATE %s", $this->config['charset'], $this->config['collation']));
            $this->connection->query('SET SESSION sql_mode = "STRICT_TRANS_TABLES"');
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function reconnect(): DatabaseInterface
    {
        return $this->disconnect()->connect();
    }

    /**
     * @return $this
     */
    public function disconnect(): DatabaseInterface
    {
        if ($this->connection) {
            $this->connection->close();
        }
        $this->connection = null;
        return $this;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function escape($value)
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

    /**
     * @return Query|QueryInterface
     */
    public function query()
    {
        return new Query($this);
    }

    /**
     * @param string $query
     * @return array|bool
     */
    public function execute(string $query)
    {
        $result = $this->connect()->connection->query($query);
        if (strpos($query, 'SELECT') === 0) {
            if ($result === false) {
                throw new \RuntimeException(
                    sprintf('Error %s: %s',
                        mysqli_errno($this->connect()->connection),
                        mysqli_error($this->connect()->connection)
                    )
                );
            }
            $data = (array)mysqli_fetch_all($result, MYSQLI_ASSOC);
            return $data;
        }
        return $result;
    }

}
