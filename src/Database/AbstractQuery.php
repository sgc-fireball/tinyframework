<?php declare(strict_types=1);

namespace TinyFramework\Database;

use Closure;

abstract class AbstractQuery implements QueryInterface
{

    protected array $select = [];

    protected string $table;

    protected ?string $class = null;

    protected array $wheres = [];

    protected array $groups = [];

    protected array $orders = [];

    protected ?int $limit = null;

    protected ?int $offset = null;

    /** @var DatabaseInterface */
    protected DatabaseInterface $driver;

    public function __construct(DatabaseInterface $driver)
    {
        $this->driver = $driver;
    }

    public function select(array $fields = [])
    {
        $this->select = $fields;
        return $this;
    }

    public function table(string $table)
    {
        $this->table = $table;
        return $this;
    }

    public function class(string $class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @param BaseModel|string $model
     * @return $this
     */
    public function byModel($model)
    {
        $this->class = is_object($model) ? get_class($model) : (string)$model;
        $this->table = $model instanceof BaseModel ? $model->getTable() : (new $this->class)->getTable();
        return $this;
    }

    /**
     * @param string|Closure $field
     * @param string|null $operation
     * @param mixed $value
     * @return $this
     */
    public function where($field, string $operation = null, $value = null)
    {
        if ($field instanceof Closure) {
            return $this->whereNested($field);
        }
        if (!is_string($field)) {
            throw new \TypeError(sprintf('Argument 1 passed to %s() must be of the type string or Closure.', __METHOD__));
        }
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => $operation,
            'value' => $value
        ];
        return $this;
    }

    /**
     * @param string|Closure $field
     * @param string $operation
     * @param mixed $value
     * @return $this
     */
    public function orWhere($field, string $operation, $value)
    {
        if ($field instanceof Closure) {
            return $this->whereNested($field);
        }
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => $operation,
            'value' => $value,
        ];
        return $this;
    }

    public function whereNested(Closure $callback)
    {
        $class = get_class($this);
        call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (count($query->wheres)) {
            $this->wheres[] = [
                'type' => 'nested',
                'boolean' => 'and',
                'query' => $query,
            ];
        }
        return $this;
    }

    public function orWhereNested(Closure $callback)
    {
        $class = get_class($this);
        call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (count($query->wheres)) {
            $this->wheres[] = [
                'type' => 'nested',
                'boolean' => 'or',
                'query' => $query
            ];
        }
        return $this;
    }

    public function orderBy($field, $order = 'asc')
    {
        $order = mb_strtolower($order);
        $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';
        $this->orders[] = ['field' => $field, 'order' => $order];
        return $this;
    }

    public function groupBy(string $field)
    {
        $this->groups[] = $field;
        return $this;
    }

    public function limit(int $limit)
    {
        $this->limit = max(1, $limit);
        return $this;
    }

    public function offset(int $offset)
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    protected function buildModels(array $results = []): array
    {
        if ($this->class && class_exists($this->class)) {
            return array_map(function (array $result) {
                return $this->buildModel($result);
            }, $results);
        }
        return $results;
    }

    /**
     * @param array $result
     * @return array|BaseModel
     */
    protected function buildModel(array $result = [])
    {
        if ($this->class && class_exists($this->class)) {
            /** @var BaseModel $model */
            $model = new $this->class();
            $model->forceFill($result);
            return $model;
        }
        return $result;
    }

    public function get(): array
    {
        return $this->buildModels($this->load());
    }

    public function first(): ?BaseModel
    {
        $result = $this->limit(1)->load();
        if (is_array($result) && isset($result[0])) {
            return $this->buildModel($result[0]);
        }
        return null;
    }

    public function firstOrFail(): BaseModel
    {
        $result = $this->limit(1)->load();
        if (is_array($result) && isset($result[0])) {
            if ($model = $this->buildModel($result[0])) {
                return $model;
            }
        }
        throw new \RuntimeException('Model not found');
    }

    public function paginate(int $perPage = 20, int $page = 1)
    {
        $count = $this->count();
        $perPage = min(max(1, $perPage), 1000);
        $pages = max(1, ceil($count / $perPage));
        $page = min(max(1, $page), $pages);
        return [
            'data' => $this->limit($perPage)->offset(($page - 1) * $perPage)->get(),
            'paging' => [
                'page' => $page,
                'pages' => $pages,
                'count' => $count,
            ]
        ];
    }

}
