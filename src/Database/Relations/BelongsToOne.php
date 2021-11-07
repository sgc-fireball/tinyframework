<?php declare(strict_types=1);

namespace TinyFramework\Database\Relations;

use TinyFramework\Database\BaseModel;
use TinyFramework\Database\QueryInterface;

// parent 1:n me
class BelongsToOne extends Relation
{

    public function __construct(
        private QueryInterface $query,
        private BaseModel $model,
        private string $foreignKey,
        private string $ownerKey,
        private string $relation
    )
    {
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

}
