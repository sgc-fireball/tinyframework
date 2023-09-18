<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// me 1:1 child
class HasOne extends Relation
{
    public function __construct(
        protected QueryInterface $query,
        protected BaseModel $model,
        private string $foreignKey,
        private string $localKey,
        private string $relation
    ) {
        parent::__construct($this->query, $this->model);
    }

    public function getLocalKey(): string
    {
        return $this->localKey;
    }

    public function getForeignKey(): string
    {
        return $this->foreignKey;
    }

    /**
     * @internal
     */
    public function load(): ?BaseModel
    {
        $model = $this->query
            ->where($this->foreignKey, '=', $this->model->{$this->localKey})
            ->first();
        $this->model->setRelation($this->relation, $model);
        return $model;
    }

    /**
     * @return BaseModel[]
     * @internal
     */
    public function eagerLoad(array $ids, array $with = []): array
    {
        return $this->query->with($with)->where($this->foreignKey, 'IN', $ids)->get();
    }
}
