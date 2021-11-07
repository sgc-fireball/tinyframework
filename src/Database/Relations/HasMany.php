<?php declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// me 1:m childs
class HasMany extends Relation
{

    public function __construct(
        private QueryInterface $query,
        private BaseModel $model,
        private string $foreignKey,
        private string $localKey,
        private string $relation
    )
    {
    }

    /**
     * @internal
     */
    public function load(): array
    {
        $models = $this->query
            ->where($this->foreignKey, '=', $this->model->{$this->localKey})
            ->get();
        $this->model->setRelation($this->relation, $models);
        return $models;
    }

}
