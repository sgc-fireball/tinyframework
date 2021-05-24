<?php declare(strict_types=1);

namespace TinyFramework\Database;

use Closure;
use RuntimeException;

abstract class QueryAwesome implements QueryInterface
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

    public function select(array $fields = []): static
    {
        $this->select = $fields;
        return $this;
    }

    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function class(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function where(string|Closure $field, string $operation = null, mixed $value = null): static
    {
        if ($field instanceof Closure) {
            return $this->whereNested($field);
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

    public function whereNull(string $field): static
    {
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => '=',
            'value' => null
        ];
        return $this;
    }

    public function whereNotNull(string $field): static
    {
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => '!=',
            'value' => null
        ];
        return $this;
    }

    public function orWhere(string|Closure $field, string $operation, mixed $value): static
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

    public function orWhereNull(string $field, string $operation, mixed $value): static
    {
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => '!=',
            'value' => null,
        ];
        return $this;
    }

    public function orWhereNotNull(string $field, string $operation, mixed $value): static
    {
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'or',
            'field' => $field,
            'operation' => '!=',
            'value' => null,
        ];
        return $this;
    }

    public function whereNested(Closure $callback): static
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

    public function orWhereNested(Closure $callback): static
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

    public function orderBy(string $field, string $order = 'asc'): static
    {
        $order = mb_strtolower($order);
        $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';
        $this->orders[] = ['field' => $field, 'order' => $order];
        return $this;
    }

    public function groupBy(string $field): static
    {
        $this->groups[] = $field;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = max(1, $limit);
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = max(0, $offset);
        return $this;
    }

    public function get(): array
    {
        return $this->buildModels($this->load());
    }

    public function first(): BaseModel|array|null
    {
        $result = $this->limit(1)->load();
        if (is_array($result) && isset($result[0])) {
            return $this->buildModel($result[0]);
        }
        return null;
    }

    public function firstOrFail(): BaseModel
    {
        if ($result = $this->first()) {
            return $result;
        }
        throw new RuntimeException('Model not found');
    }

    public function paginate(int $perPage = 20, int $page = 1): array
    {
        $count = $this->count();
        $perPage = max(1, $perPage);
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

    public function each(callable $callable, int $chunk = 1000): array
    {
        $next = 1;
        $results = [];
        do {
            $paginate = $this->paginate($chunk, $next);
            foreach ($paginate['data'] as &$item) {
                call_user_func($callable, $results[] = $this->buildModel($item));
            }
            $next++;
        } while ($next <= $paginate['paging']['pages']);
        return $results;
    }

    public function filter(callable $callable, int $chunk = 1000): array
    {
        $next = 1;
        $results = [];
        do {
            $paginate = $this->paginate($chunk, $next);
            foreach ($paginate['data'] as &$item) {
                if (call_user_func($callable, $item = $this->buildModel($item))) {
                    $results[] = $item;
                }
            }
            $next++;
        } while ($next <= $paginate['paging']['pages']);
        return $results;
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

    protected function buildModel(array $result = []): array|BaseModel
    {
        if ($this->class && class_exists($this->class)) {
            /** @var BaseModel $model */
            $model = new $this->class();
            $model->forceFill($result);
            return $model;
        }
        return $result;
    }

    public function byModel(BaseModel|string $model): static
    {
        $class = is_object($model) ? get_class($model) : (string)$model;
        if (!class_exists($class)) {
            throw new RuntimeException('Class does not exists: ' . $class);
        }
        $this->class = $class;
        $model = $model instanceof BaseModel ? $model : (new $this->class);
        if (!method_exists($model, 'getTable')) {
            throw new RuntimeException('Missing method ' . $class . '@getTable.');
        }
        $this->table = $model->getTable();
        return $this;
    }

}
