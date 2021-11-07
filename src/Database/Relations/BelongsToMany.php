<?php declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// me n:m parent
// parent n:m me
class BelongsToMany extends Relation
{

    public function __construct(
        private QueryInterface $query,
        private BaseModel $model,
        private string $table,
        private string $foreignPivotKey,
        private string $relatedPivotKey,
        private string $parentKey,
        private string $relatedKey,
        private string $relationName
    )
    {
    }

    /**
     * @internal
     */
    public function load(): array
    {
        $query = $this->query
            ->select([$this->query->raw(sprintf('`%s`.%s', $this->query->table(), '*'))])
            ->leftJoin($this->query->table(), 'id', $this->table, $this->relatedPivotKey)
            ->where($this->query->raw(sprintf('`%s`.`%s`', $this->table, $this->foreignPivotKey)), '=', $this->model->id);
        $models = $query->get();
        $this->model->setRelation($this->relationName, $models);
        return $models;
    }

}
