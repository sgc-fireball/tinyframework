<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// parent 1:n me
class BelongsToOne extends Relation
{
    public function __construct(
        protected QueryInterface $query,
        protected BaseModel $model,
        private string $foreignKey,
        private string $ownerKey,
        private string $relation
    ) {
        parent::__construct($this->query, $this->model);
    }

    public function getOwnerKey(): string
    {
        return $this->ownerKey;
    }

    /**
     * @internal
     */
    public function load(): ?BaseMOdel
    {
        $model = $this->query
            ->where($this->foreignKey, '=', $this->model->{$this->ownerKey})
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
        return $this->query
            ->where($this->foreignKey, 'IN', $ids)
            ->get();
    }
}
