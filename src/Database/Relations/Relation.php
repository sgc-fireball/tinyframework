<?php

declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

abstract class Relation
{
    public function __construct(
        protected QueryInterface $query,
        protected BaseModel $model
    ) {
    }

    abstract public function load(): mixed;

    abstract public function eagerLoad(array $ids, array $with = []): mixed;

    public function getBlankTargetObject(): BaseModel
    {
        $class = $this->query->class();
        return new $class();
    }
}
