<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\DatabaseInterface;
use TinyFramework\Database\QueryInterface;

// me n:m parent
// parent n:m me
class BelongsToMany extends Relation
{
    private DatabaseInterface $pivotConnection;

    public function __construct(
        protected QueryInterface $query,
        protected BaseModel $model,
        private string $table,
        private string $foreignPivotKey,
        private string $relatedPivotKey,
        private string $parentKey,
        private string $relatedKey,
        private string $relationName,
        string $pivotConnection = null
    ) {
        parent::__construct($this->query, $this->model);
        $this->pivotConnection = container('database.' . ($pivotConnection ?? config('database.default')));
    }

    public function getParentKey(): string
    {
        return $this->parentKey;
    }

    /**
     * @internal
     */
    public function load(): array
    {
        assert(!empty($this->model->id));
        $pivotRows = $this->pivotConnection->query()
            ->table($this->table)
            ->where($this->foreignPivotKey, '=', $this->model->{$this->parentKey})
            ->get();
        $ids = array_map(fn (array $row) => $row[$this->relatedPivotKey], $pivotRows);
        $models = $this->query
            ->where($this->relatedKey, 'IN', $ids)
            ->get();
        // @TODO set pivot data
        $this->model->setRelation($this->relationName, $models);
        return $models;
    }

    /**
     * @return array<string, array<string, BaseModel>>
     * @internal
     */
    public function eagerLoad(array $ids, array $with = []): array
    {
        $pivotRows = $this->pivotConnection->query()
            ->table($this->table)
            ->where($this->foreignPivotKey, 'IN', $ids)
            ->get();
        $ids = array_map(fn (array $pivotRow) => $pivotRow[$this->relatedPivotKey], $pivotRows);
        /** @var BaseModel[] $models */
        $models = $this->query
            ->where($this->relatedKey, 'IN', $ids)
            ->get();
        $results = [];
        foreach ($pivotRows as $pivotRow) {
            foreach ($models as $model) {
                if ($model->{$this->relatedKey} !== $pivotRow[$this->relatedPivotKey]) {
                    continue;
                }
                $clone = clone $model;
                $clone->pivot($pivotRow);
                $results[$pivotRow[$this->foreignPivotKey]] ??= [];
                $results[$pivotRow[$this->foreignPivotKey]][$model->{$this->relatedKey}] = $clone;
            }
        }
        return $results;
    }
}
