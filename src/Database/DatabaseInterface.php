<?php declare(strict_types=1);

namespace TinyFramework\Database;

interface DatabaseInterface
{

    public function __construct(array $config = []);

    /**
     * @return DatabaseInterface
     */
    public function connect(): DatabaseInterface;

    /**
     * @return DatabaseInterface
     */
    public function reconnect(): DatabaseInterface;

    /**
     * @return DatabaseInterface
     */
    public function disconnect(): DatabaseInterface;

    public function escape($value);

    /**
     * @return QueryInterface
     */
    public function query();

    /**
     * @param string $query
     * @return array|bool
     */
    public function execute(string $query);

}
