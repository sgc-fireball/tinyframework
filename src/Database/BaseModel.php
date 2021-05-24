<?php declare(strict_types=1);

namespace TinyFramework\Database;

use ArrayAccess;
use JsonSerializable;

class BaseModel implements JsonSerializable, ArrayAccess
{

    protected string $table;

    protected string $primaryType = 'uuid';

    protected array $fillable = [];

    protected array $attributes = [];

    protected array $originals = [];

    public function __construct(array $attributes = [])
    {
        $this->table = $this->table ?? mb_strtolower(basename(str_replace('\\', '/', get_class($this))));
        $this->originals = $this->attributes = $attributes;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function fill(array $attributes = [], bool $force = false): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || $force) {
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
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
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
        return array_key_exists((string)$offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[(string) $offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[(string)$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->attributes[(string) $offset] = null;
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
        /** @var DatabaseInterface $database */
        $database = container('database');
        return $database->query()->table((new $class())->getTable())->class($class);
    }

    public function save(): static
    {
        if (!array_key_exists('id', $this->attributes) || empty($this->attributes['id'])) {
            if ($this->primaryType === 'string') {
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
        if (array_key_exists('id', $this->attributes) && !empty($this->attributes['id'])) {
            $this::query()->where('id', '=', $this->attributes['id'])->delete();
        }
        $this->originals = [];
        return $this;
    }

}
