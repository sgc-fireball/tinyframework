<?php declare(strict_types=1);

namespace TinyFramework\Database;

use ArrayAccess;
use JsonSerializable;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Database\Relations\BelongsToOne;
use TinyFramework\Database\Relations\HasMany;
use TinyFramework\Database\Relations\HasOne;
use TinyFramework\Database\Relations\Relation;
use TinyFramework\Helpers\Str;

class BaseModel implements JsonSerializable, ArrayAccess
{

    protected string $connection;

    protected string $table;

    protected string $primaryType = 'uuid';

    protected array $fillable = [];

    // @TODO protected array $casts = []

    protected array $attributes = [];

    protected array $originals = [];

    protected array $relations = [];

    public function __construct(array $attributes = [])
    {
        $this->connection ??= config('database.default');
        $this->table ??= mb_strtolower(basename(str_replace('\\', '/', \get_class($this))));
        $this->originals = $this->attributes = $attributes;
    }

    public function getConnection(): DatabaseInterface
    {
        return container('database.' . $this->connection);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function fill(array $attributes = [], bool $force = false): static
    {
        foreach ($attributes as $key => $value) {
            if (\in_array($key, $this->fillable) || $force) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @internal use only from Database
     */
    public function forceFill(array $attributes = []): static
    {
        $this->attributes = $this->originals = $attributes;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    public function __get(string $name): mixed
    {
        if (method_exists($this, $name)) {
            $methodReflection = new \ReflectionMethod($this, $name);
            $reflectionType = $methodReflection->getReturnType();
            if (!$reflectionType) {
                return null;
            }
            if (!is_subclass_of((string)$reflectionType, Relation::class)) {
                return null;
            }
            if (\array_key_exists($name, $this->relations)) {
                return $this->relations[$name];
            }
            /** @var HasOne|HasMany|BelongsToOne|BelongsToMany $relation */
            $relation = $this->{$name}();
            assert($relation instanceof Relation);
            return $relation->load();
        }
        $method = Str::factory($name)->camelCase()->ucfirst()->prefix('get')->postfix('Attribute')->string();
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }
        // @TODO \array_key_exists($this->casts[$name])
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return \array_key_exists($name, $this->attributes);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists((string)$offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[(string)$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[(string)$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->attributes[(string)$offset] = null;
    }

    public function isDirty(): bool
    {
        if (!array_key_exists('id', $this->attributes) || !$this->attributes['id']) {
            return true;
        }
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->originals)) {
                return true;
            }
            if ($this->originals[$key] !== $value) {
                return true;
            }
        }
        return false;
    }

    public static function query(): QueryInterface
    {
        $class = static::class;
        $model = new $class();
        return $model
            ->getConnection()
            ->query()
            ->table($model->getTable())
            ->class($class);
    }

    public function save(): static
    {
        if (!array_key_exists('id', $this->attributes) || empty($this->attributes['id'])) {
            if ($this->primaryType === 'uuid') {
                $this->attributes['id'] = guid();
            }
        }
        if (!$this->isDirty()) {
            return $this;
        }
        $this::query()->put($this->attributes);
        $this->originals = $this->attributes;
        return $this;
    }

    public function delete(): static
    {
        if (\array_key_exists('id', $this->attributes) && !empty($this->attributes['id'])) {
            $this::query()->where('id', '=', $this->attributes['id'])->delete();
        }
        $this->originals = [];
        return $this;
    }

    public function setRelation(string $field, BaseModel|array|null $value = null): static
    {
        $this->relations[$field] = $value;
        return $this;
    }

    public function getRelation(string $field): BaseModel|array|null
    {
        return $this->relations[$field] ?? null;
    }

    protected function hasOne(
        string $class,
        string $foreignKey = null,
        string $localKey = null
    ): HasOne
    {
        [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return new HasOne(
            $this->getRelatedQuery($class),
            $this,
            $foreignKey ?: Str::factory(class_basename($this))->snakeCase() . '_id',
            $localKey ?: 'id',
            $caller['function']
        );
    }

    protected function hasMany(
        string $class,
        string $foreignKey = null,
        string $localKey = null
    ): HasMany
    {
        [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return new HasMany(
            $this->getRelatedQuery($class),
            $this,
            $foreignKey ?? Str::factory(class_basename($this))->snakeCase() . '_id',
            $localKey ?? 'id',
            $caller['function']
        );
    }

    protected function belongsToOne(
        string $class,
        string $foreignKey = 'id',
        string $ownerKey = null
    ): BelongsToOne
    {
        [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $ownerKey ??= Str::factory(class_basename($class))->snakeCase() . '_id';
        return new BelongsToOne(
            $this->getRelatedQuery($class),
            $this,
            $foreignKey,
            $ownerKey,
            $caller['function']
        );
    }

    protected function belongsToMany(
        string $class,
        string $table = null,
        string $foreignPivotKey = null,
        string $relatedPivotKey = null,
        string $parentKey = 'id',
        string $relatedKey = 'id'
    ): BelongsToMany
    {
        $tableA = $this->getTable();
        $tableB = (new $class())->getTable();
        $table ??= $tableA < $tableB ? $tableA . '_2_' . $tableB : $tableB . '_2_' . $tableA;
        [$one, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return new BelongsToMany(
            $this->getRelatedQuery($class),
            $this,
            $table,
            $foreignPivotKey ?: $tableA . '_id',
            $relatedPivotKey ?: $tableB . '_id',
            $parentKey,
            $relatedKey,
            $caller['function']
        );
    }

    protected function getRelatedQuery(string $class): QueryInterface
    {
        if (!(is_subclass_of($class, BaseModel::class))) {
            throw new \RuntimeException($class . ' must be an instance of ' . BaseModel::class);
        }
        $model = new $class();
        return $model
            ->getConnection()
            ->query()
            ->table($model->getTable())
            ->class($class);
    }

}
