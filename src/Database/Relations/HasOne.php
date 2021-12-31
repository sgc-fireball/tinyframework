<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// me 1:1 child
class HasOne extends Relation
{
    public function __construct(
        private QueryInterface $query,
        private BaseModel $model,
        private string $foreignKey,
        private string $localKey,
        private string $relation
    ) {
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
}
