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
        $this->table = $this->table ?? strtolower(basename(str_replace('\\', '/', get_class($this))));
        $this->originals = $this->attributes = $attributes;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function fill(array $attributes = [], bool $force = false)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || $force) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     * @internal use only from Database
     */
    public function forceFill(array $attributes = [])
    {
        $this->attributes = $this->originals = $attributes;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->attributes;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->attributes[$offset] = null;
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

    public function save(): self
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

    public function delete(): self
    {
        if (array_key_exists('id', $this->attributes) && !empty($this->attributes['id'])) {
            $this::query()->where('id', '=', $this->attributes['id'])->delete();
        }
        $this->originals = [];
        return $this;
    }

}
