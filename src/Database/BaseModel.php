<?php

declare(strict_types=1);

namespace TinyFramework\Database;

use ArrayAccess;
use DateTime;
use JsonSerializable;
use TinyFramework\Cast\CastInterface;
use TinyFramework\Database\Relations\BelongsToMany;
use TinyFramework\Database\Relations\BelongsToOne;
use TinyFramework\Database\Relations\HasMany;
use TinyFramework\Database\Relations\HasOne;
use TinyFramework\Database\Relations\Relation;
use TinyFramework\Helpers\Str;

/**
 * @property string $id
 * @property DateTime $created_at
 * @property DateTime|null $updated_at
 */
class BaseModel implements JsonSerializable, ArrayAccess
{
    protected string $connection;

    protected string $table;

    protected string $primaryType = 'uuid';

    protected array $hidden = [];

    protected array $fillable = [];

    protected array $casts = [];

    protected array $attributes = [];

    protected array $originals = [];

    protected array $relations = [];

    protected array $with = [];

    protected ?array $pivot = null;

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
                $this->__set((string)$key, $value);
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
        return $this->toArray();
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
        $value = $this->attributes[$name] ?? null;
        if ($value === null) {
            return null;
        }
        $type = strtolower($this->casts[$name] ?? '') ?: '';
        if ($type === '') {
            return $value;
        }
        if (str_starts_with($type, 'encrypted:')) {
            $value = crypto()->decrypt($value);
            $type = substr($type, 10);
            if (in_array($type, ['array', 'object', 'date', 'datetime'])) {
                $value = $value ? @unserialize($value) : null;
            }
        }
        if (in_array($type, ['bool', 'boolean'])) {
            return (bool)$value;
        } elseif (in_array($type, ['int', 'integer', 'timestamp'])) {
            return (int)$value;
        } elseif (in_array($type, ['float', 'double'])) {
            return (float)$value;
        } elseif (str_starts_with($type, 'decimal:')) {
            return number_format((float)$value, (int)explode(':', $type, 2)[1]);
        } elseif ($type === 'array') {
            return is_array($value) ? $value : null;
        } elseif ($type === 'json') {
            return @json_decode($value) ?: null;
        } elseif ($type === 'object') {
            return is_object($value) ? $value : null;
        } elseif (in_array($type, ['date', 'datetime'])) {
            $value = $value instanceof DateTime ? $value : null;
        } elseif (class_exists($type)) {
            return new $type($value);
        }
        return $value;
    }

    public function __isset(string $name): bool
    {
        return \array_key_exists($name, $this->attributes);
    }

    public function __set(string $name, mixed $value): void
    {
        if ($value !== null) {
            $type = strtolower($this->casts[$name] ?? '') ?: '';
            $encrypt = str_starts_with($type, 'encrypted:');
            if ($encrypt) {
                $type = substr($type, 10);
            }
            if (in_array($type, ['bool', 'boolean'])) {
                $value = (bool)$value;
            } elseif (in_array($type, ['int', 'integer'])) {
                $value = (int)$value;
            } elseif (in_array($type, ['float', 'double'])) {
                $value = (float)$value;
            } elseif (str_starts_with($type, 'decimal:')) {
                $value = number_format((float)$value, (int)explode(':', $type, 2)[1]);
            } elseif ($type === 'array') {
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $value = $value->toArray();
                } elseif (is_string($value)) {
                    if (
                        (str_starts_with($value, '[') && str_ends_with($value, ']')) ||
                        (str_starts_with($value, '{') && str_ends_with($value, '}'))
                    ) {
                        $value = json_decode($value, true);
                    }
                }
                if (!is_array($value)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Value %for s->%s must be an array.',
                            get_class($this),
                            $name
                        )
                    );
                }
            } elseif ($type === 'json') {
                $value = json_encode($type);
            } elseif ($type === 'object') {
                if (is_string($value) && (str_starts_with($value, '{') && str_ends_with($value, '}'))) {
                    $value = json_decode($value);
                }
                if (!is_object($value)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Value for %s->%s must be an object.',
                            get_class($this),
                            $name
                        )
                    );
                }
            } elseif (in_array($type, ['date', 'datetime', 'timestamp'])) {
                if (is_null($value)) {
                    // do nothing - null is fine!
                } elseif (is_numeric($value)) {
                    $value = DateTime::createFromFormat('u', $value);
                } elseif (is_string($value) && $value) {
                    if ($time = strtotime($value)) {
                        $value = (new Datetime())->setTimestamp($time);
                    }
                }
                if (!($value instanceof DateTime)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Value for %s->%s must be an integer, a parseable strtorime value or an \DateTime object.',
                            get_class($this),
                            $name
                        )
                    );
                }
                if ($type === 'date') {
                    $value->setTime(0, 0, 0);
                } elseif ($type === 'timestamp') {
                    $value = $value->getTimestamp();
                }
            } elseif (class_exists($type)) {
                if (!($value instanceof $type)) {
                    $value = null;
                } elseif (method_exists($value, '__toString')) {
                    $value = $value->__toString();
                }
            }
            if ($encrypt) {
                if (in_array($type, ['array', 'object', 'date', 'datetime'])) {
                    $value = serialize($value);
                }
                $value = crypto()->encrypt($value);
            }
        }
        $this->attributes[$name] = $value;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $result[$key] = $this->__get($key);
            }
        }
        foreach ($this->relations as $relation => $items) {
            $result[$relation] = [];
            foreach ($items as $index => $item) {
                $result[$relation][$index] = $item->toArray();
            }
        }
        return $result;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists((string)$offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string)$offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set((string)$offset, $value);
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
            ->class($class)
            ->with($model->with());
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
        if (array_key_exists('created_at', $this->attributes) && !$this->attributes['created_at']) {
            $this->created_at = now();
        }
        if (array_key_exists('updated_at', $this->attributes)) {
            $this->updated_at = now();
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

    public function reload(): static
    {
        if (\array_key_exists('id', $this->attributes) && !empty($this->attributes['id'])) {
            $data = $this::query()
                ->where('id', '=', $this->attributes['id'])
                ?->first()?->attributes ?? [];
            $this->relations = $this->pivot = [];
            $this->attributes = $this->originals = $data;
        }
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
    ): HasOne {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
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
    ): HasMany {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
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
    ): BelongsToOne {
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
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
    ): BelongsToMany {
        $tableA = $this->getTable();
        $tableB = (new $class())->getTable();
        $table ??= $tableA < $tableB ? $tableA . '_2_' . $tableB : $tableB . '_2_' . $tableA;
        $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
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

    public function with(array|string|null $paths = null): array|static
    {
        if ($paths === null) {
            return $this->with;
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

    public function pivot(array|null $pivot = null): array|null|static
    {
        if ($pivot === null) {
            return $this->pivot;
        }
        $this->pivot = $pivot;
        return $this;
    }
}
