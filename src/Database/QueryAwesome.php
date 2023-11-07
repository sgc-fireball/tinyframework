<?php

declare(strict_types=1);

namespace TinyFramework\Database;

use Closure;
use RuntimeException;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Database\Relations\BelongsToOne;
use TinyFramework\Database\Relations\HasMany;
use TinyFramework\Database\Relations\HasOne;
use TinyFramework\Database\Relations\Relation;
use TinyFramework\Helpers\DatabaseRaw;

abstract class QueryAwesome implements QueryInterface
{
    protected array $select = [];

    protected string $table;

    protected ?string $class = null;

    protected array $joins = [];

    protected array $wheres = [];

    protected array $groups = [];

    protected array $orders = [];

    protected ?int $limit = null;

    protected ?int $offset = null;

    protected array $with = [];

    protected DatabaseInterface $driver;

    public function __construct(DatabaseInterface $driver)
    {
        $this->driver = $driver;
    }

    public function raw(string $str): DatabaseRaw
    {
        return new DatabaseRaw($str);
    }

    public function select(array $fields = []): static
    {
        $this->select = $fields;
        return $this;
    }

    public function table(string $table = null): static|string|null
    {
        if ($table === null) {
            return $this->table;
        }
        $this->table = $table;
        return $this;
    }

    public function class(string $class = null): static|string|null
    {
        if ($class === null) {
            return $this->class;
        }
        if (!class_exists($class)) {
            throw new \InvalidArgumentException('Argument #1 must be an existing class.');
        }
        if (!is_subclass_of($class, BaseModel::class)) {
            throw new \InvalidArgumentException('Argument #1 must be an subclass of ' . BaseModel::class . '.');
        }
        $this->class = $class;
        return $this;
    }

    public function innerJoin(string $tableA, string $fieldA, string $tableB, string $fieldB): QueryInterface
    {
        $this->joins[] = [
            'type' => 'INNER',
            'tableA' => $tableA,
            'fieldA' => $fieldA,
            'tableB' => $tableB,
            'fieldB' => $fieldB,
        ];
        return $this;
    }


    public function outerJoin(string $tableA, string $fieldA, string $tableB, string $fieldB): QueryInterface
    {
        $this->joins[] = [
            'type' => 'OUTER',
            'tableA' => $tableA,
            'fieldA' => $fieldA,
            'tableB' => $tableB,
            'fieldB' => $fieldB,
        ];
        return $this;
    }

    public function rightJoin(string $tableA, string $fieldA, string $tableB, string $fieldB): QueryInterface
    {
        $this->joins[] = [
            'type' => 'RIGHT',
            'tableA' => $tableA,
            'fieldA' => $fieldA,
            'tableB' => $tableB,
            'fieldB' => $fieldB,
        ];
        return $this;
    }

    public function leftJoin(string $tableA, string $fieldA, string $tableB, string $fieldB): QueryInterface
    {
        $this->joins[] = [
            'type' => 'LEFT',
            'tableA' => $tableA,
            'fieldA' => $fieldA,
            'tableB' => $tableB,
            'fieldB' => $fieldB,
        ];
        return $this;
    }

    public function where(
        string|DatabaseRaw|Closure $field,
        string $operation = null,
        mixed $value = null
    ): QueryInterface {
        if ($field instanceof Closure) {
            return $this->whereNested($field);
        }
        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'and',
            'field' => $field,
            'operation' => $operation,
            'value' => $value,
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
            'value' => null,
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
            'value' => null,
        ];
        return $this;
    }

    public function orWhere(string|DatabaseRaw|Closure $field, string $operation, mixed $value): static
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
        $class = \get_class($this);
        \call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (\count($query->wheres)) {
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
        $class = \get_class($this);
        \call_user_func($callback, $query = (new $class($this->driver))->table($this->table));
        if (\count($query->wheres)) {
            $this->wheres[] = [
                'type' => 'nested',
                'boolean' => 'or',
                'query' => $query,
            ];
        }
        return $this;
    }

    public function orderBy(string $field, string $order = 'asc'): static
    {
        $order = mb_strtolower($order);
        $order = \in_array($order, ['asc', 'desc']) ? $order : 'asc';
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
        $rows = $this->load();
        $models = $this->buildModels($rows);
        $this->eagerLoading($models);
        return $models;
    }

    public function first(): BaseModel|array|null
    {
        $result = $this->limit(1)->load();
        if (isset($result[0]) && \is_array($result[0])) {
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
            ],
        ];
    }

    public function each(callable $callable, int $chunk = 1000): array
    {
        $next = 1;
        $results = [];
        do {
            $paginate = $this->paginate($chunk, $next);
            foreach ($paginate['data'] as &$item) {
                \call_user_func($callable, $results[] = $this->buildModel($item));
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
                if (\call_user_func($callable, $item = $this->buildModel($item))) {
                    $results[] = $item;
                }
            }
            $next++;
        } while ($next <= $paginate['paging']['pages']);
        return $results;
    }

    protected function buildModels(array $results = []): array
    {
        if ($this->class) {
            return array_map(function (array $result) {
                return $this->buildModel($result);
            }, $results);
        }
        return $results;
    }

    protected function buildModel(array $result = []): array|BaseModel
    {
        if ($this->class) {
            $class = $this->class;
            $model = new $class();
            $model->forceFill($result);
            return $model;
        }
        return $result;
    }

    public function byModel(BaseModel|string $model): static
    {
        $class = \is_object($model) ? \get_class($model) : (string)$model;
        $this->class($class);
        $model = $model instanceof BaseModel ? $model : (new $this->class());
        $this->table($model->getTable());
        return $this;
    }

    public function with(array|string|null $paths = null): static|array
    {
        if ($paths === null) {
            return $this->with;
        }
        if (empty($paths)) {
            return $this;
        }
        assignEagerLoadingPaths(
            $this,
            $this->with,
            $paths,
            function (BaseModel $model, string $relation): void {
                $reflectionClass = new \ReflectionClass(get_class($model));
                $message = sprintf(
                    'Invalid relation call (%s::%s).',
                    $reflectionClass->getName(),
                    $relation
                );
                if (!$reflectionClass->hasMethod($relation)) {
                    throw new \InvalidArgumentException($message . ' Missing method.');
                }
                $reflectionMethod = $reflectionClass->getMethod($relation);
                if (!$reflectionMethod->isPublic()) {
                    throw new \InvalidArgumentException($message . ' Relation is not public.');
                }
                if (!$reflectionMethod->hasReturnType()) {
                    throw new \InvalidArgumentException($message . ' Method has no return type.');
                }
                $reflectionNamedType = $reflectionMethod->getReturnType();
                if (!is_subclass_of($reflectionNamedType->getName(), Relation::class)) {
                    throw new \InvalidArgumentException(
                        $message . ' Return type mismatch or doesn\'t extends ' . Relation::class
                    );
                }
            }
        );
        return $this;
    }

    /**
     * @param BaseModel[] $models
     * @return void
     */
    private function eagerLoading(array &$models): void
    {
        if (!count($models) || !count($this->with)) {
            return;
        }
        foreach ($this->with as $with => $subWith) {
            if (!method_exists($models[0], $with)) {
                throw new \RuntimeException('Unknown relation method ' . get_class($models[0]) . '::' . $with);
            }
            /** @var Relation $relation */
            $relation = [$models[0], $with]();
            if ($relation instanceof HasOne) {
                $this->eagerLoadingHasOne($relation, $models, $with, $subWith);
            } elseif ($relation instanceof HasMany) {
                $this->eagerLoadingHasMany($relation, $models, $with, $subWith);
            } elseif ($relation instanceof BelongsToOne) {
                $this->eagerLoadingBelongsToOne($relation, $models, $with, $subWith);
            } elseif ($relation instanceof BelongsToMany) {
                $this->eagerLoadingBelongsToMany($relation, $models, $with, $subWith);
            } else {
                throw new \RuntimeException(
                    'Unknown relation type ' . get_class($relation) . ' from ' . get_class($models[0]) . '::' . $with
                );
            }
        }
    }

    /**
     * @param HasOne $relation
     * @param BaseModel[] $models
     * @param string $with
     * @param string[] $subWith
     * @return void
     */
    private function eagerLoadingHasOne(HasOne $relation, array &$models, string $with, array $subWith): void
    {
        $ids = [];
        foreach ($models as $model) {
            /** @var HasOne $relation */
            $relation = $model->{$with}();
            $ids[] = $model->{$relation->getLocalKey()};
        }
        $relationModels = $relation->eagerLoad($ids, $subWith);
        foreach ($models as $model) {
            $relationModel = array_values(
                array_filter($relationModels, function (BaseModel $relationModel) use ($model, $relation) {
                    return $model->id === $relationModel->{$relation->getForeignKey()};
                })
            );
            $model->setRelation($with, count($relationModel) === 1 ? $relationModel[0] : null);
        }
    }

    /**
     * @param HasMany $relation
     * @param BaseModel[] $models
     * @param string $with
     * @param string[] $subWith
     * @return void
     */
    private function eagerLoadingHasMany(HasMany $relation, array &$models, string $with, array $subWith): void
    {
        $ids = [];
        foreach ($models as $model) {
            /** @var HasMany $relation */
            $relation = $model->{$with}();
            $ids[] = $model->{$relation->getLocalKey()};
        }
        $relationModels = $relation->eagerLoad($ids, $subWith);
        foreach ($models as $model) {
            $relationModel = array_values(
                array_filter($relationModels, function (BaseModel $relationModel) use ($model, $relation) {
                    return $model->id === $relationModel->{$relation->getForeignKey()};
                })
            );
            $model->setRelation($with, $relationModel);
        }
    }

    /**
     * @param BelongsToOne $relation
     * @param BaseModel[] $models
     * @param string $with
     * @param string[] $subWith
     * @return void
     */
    private function eagerLoadingBelongsToOne(
        BelongsToOne $relation,
        array &$models,
        string $with,
        array $subWith
    ): void {
        $mapping = [];
        foreach ($models as $model) {
            /** @var BelongsToOne $relation */
            $relation = $model->{$with}();
            $ownerId = $model->{$relation->getOwnerKey()};
            $mapping[$model->id] ??= [];
            $mapping[$model->id][] = $ownerId;
        }

        $ids = array_unique(array_merge(...array_values($mapping)));
        $relationModels = $relation->eagerLoad($ids, $subWith);
        foreach ($models as $model) {
            $relationModel = array_values(
                array_filter($relationModels, function (BaseModel $relationModel) use ($model, $mapping) {
                    return in_array($relationModel->id, $mapping[$model->id]);
                })
            );
            $model->setRelation($with, count($relationModel) === 1 ? $relationModel[0] : null);
        }
    }

    /**
     * @param BelongsToMany $relation
     * @param BaseModel[] $models
     * @param string $with
     * @param string[] $subWith
     * @return void
     */
    private function eagerLoadingBelongsToMany(
        BelongsToMany $relation,
        array &$models,
        string $with,
        array $subWith
    ): void {
        $ids = [];
        foreach ($models as $model) {
            /** @var BelongsToMany $relation */
            $relation = $model->{$with}();
            $ids[] = $model->{$relation->getParentKey()};
        }
        $relationModels = $relation->eagerLoad($ids, $subWith);
        foreach ($models as $model) {
            $model->setRelation($with, $relationModels[$model->{$relation->getParentKey()}] ?? []);
        }
    }
}
