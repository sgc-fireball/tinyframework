<?php declare(strict_types=1);

namespace TinyFramework\Helpers;

/**
 * @see https://www.php.net/manual/de/ref.array.php
 */
class Arr implements \ArrayAccess, \Iterator
{

    protected array $items = [];

    public static function factory(array $items = []): Arr
    {
        return new self($items);
    }

    public static function wrap(mixed $value): Arr
    {
        if ($value === null) {
            $value = [];
        }
        $value = \is_array($value) ? $value : [$value];
        return new self($value);
    }

    public static function fill(int $start_index, int $num, mixed $value): Arr
    {
        return new self(\array_fill($start_index, $num, $value));
    }

    public static function range(mixed $start, mixed $end, int|float $step = 1): Arr
    {
        return new self(\range($start, $end, $step));
    }

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function __toArray(): array
    {
        return $this->items;
    }

    public function __get(string $name): mixed
    {
        return $this->items[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->items[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return \array_key_exists($name, $this->items);
    }

    public function __unset(string $name): void
    {
        if (\array_key_exists($name, $this->items)) {
            unset($this->items[$name]);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->items);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function current(): mixed
    {
        return \current($this->items);
    }

    public function next(): void
    {
        \next($this->items);
    }

    public function key(): int|null|string
    {
        return \key($this->items);
    }

    public function valid(): bool
    {
        return $this->current() !== false;
    }

    public function rewind(): void
    {
        \reset($this->items);
    }

    public function changeKeyCase(int $case = CASE_LOWER): Arr
    {
        return new self(\array_change_key_case($this->items, $case));
    }

    public function chunk(int $size, bool $preserve_keys = false): Arr
    {
        return new self(\array_chunk($this->items, $size, $preserve_keys));
    }

    public function column(int|string|null $column_key, int|string|null $index_key = null): Arr
    {
        return new self(\array_column($this->items, $column_key, $index_key));
    }

    public function combineWithValues(array $values): Arr
    {
        return new self(\array_combine($this->items, $values));
    }

    public function combineWithKeys(array $keys): Arr
    {
        return new self(\array_combine($keys, $this->items));
    }

    public function countBy(\Closure $closure = null): Arr
    {
        throw new \RuntimeException('Currently not supported!'); // @TODO
    }

    public function countValues(): Arr
    {
        return new self(\array_count_values($this->items));
    }

    public function diffAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self((array)\call_user_func_array('array_diff_assoc', $arrays));
    }

    public function diffUAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self((array)\call_user_func_array('\\array_diff_uassoc', $arrays));
    }

    public function diffKey(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self((array)\call_user_func_array('\\array_diff_key', $arrays));
    }

    public function diffUKey(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self((array)\call_user_func_array('\\array_diff_ukey', $arrays));
    }

    public function diff(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self((array)\call_user_func_array('\\array_diff', $arrays));
    }

    public function flatten(int $depth = -1): Arr
    {
        $depth = $depth === -1 ? PHP_INT_MAX : $depth;
        $result = [];
        foreach ($this->items as $item) {
            $item = $item instanceof Arr ? (array)$item : $item;
            if (!\is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1 ? \array_values($item) : (new self($item))->flatten($depth - 1);
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        return new self($result);
    }

    public function filter(callable $callback): Arr
    {
        return new self(\array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
    }

    public function first(callable $callback = null): mixed
    {
        if (\is_callable($callback)) {
            foreach ($this->items as $key => $item) {
                if ($callback($item, $key)) {
                    return $item;
                }
            }
            return null;
        }
        foreach ($this->items as $key => $item) {
            return $item;
        }
        return null;
    }

    public function flip(callable $callback): Arr
    {
        return new self(\array_flip($this->items));
    }

    public function intersectAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self(\call_user_func_array('\\array_intersect_assoc', $arrays));
    }

    public function intersectUAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_intersect_uassoc', $arrays));
    }

    public function intersectByKeys(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self(\call_user_func_array('\\array_intersect_key', $arrays));
    }

    public function intersectUKey(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_intersect_ukey', $arrays));
    }

    public function intersect(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self(\call_user_func_array('\\array_intersect', $arrays));
    }

    public function keyExists(mixed $key): bool
    {
        return \array_key_exists($key, $this->items);
    }

    public function keyFirst(): string|int|null
    {
        return \array_key_first($this->items);
    }

    public function keyLast(): string|int|null
    {
        return \array_key_last($this->items);
    }

    public function keys(): Arr
    {
        return new self(\array_keys($this->items));
    }

    public function last(callable $callback = null): mixed
    {
        if (\is_callable($callback)) {
            return (new self(\array_reverse($this->items, true)))->first($callback);
        }
        return \count($this->items) ? \end($this->items) : null;
    }

    public function map(callable $callback): Arr
    {
        return new self(\array_map($callback, $this->items));
    }

    public function transform(callable $callback): static
    {
        $this->items = \array_map($callback, $this->items);
        return $this;
    }

    public function mapWithKeys(callable $callback): Arr
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);
            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }
        return new self($result);
    }

    public function transformWithKeys(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);
            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }
        $this->items = $result;
        return $this;
    }

    public function mergeRecursive(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self(\call_user_func_array('\\array_merge_recursive', $arrays));
    }

    public function merge(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        return new self(\call_user_func_array('\\array_merge', $arrays));
    }

    public function multisort(mixed $array1_sort_order = SORT_ASC, mixed $array1_sort_flags = SORT_REGULAR, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array1_sort_order);
        \array_unshift($arrays, $array1_sort_flags);
        return new self(\call_user_func_array('\\array_multisort', $arrays));
    }

    public function pad(int $size, mixed $value = null): Arr
    {
        return new self(\array_pad($this->items, $size, $value));
    }

    public function pop(): mixed
    {
        return \array_pop($this->items);
    }

    public function product(): int|float
    {
        return \array_product($this->items);
    }

    public function push(...$values): static
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }
        return $this;
    }

    public function concat(array $source): Arr
    {
        $arr = new self($this->items);
        foreach ($source as $item) {
            $this->items[] = $item;
        }
        return $arr;
    }

    public function random(int $num = 1): mixed
    {
        return \array_rand($this->items, $num);
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $result = \array_reduce($this->items, $callback, $initial);
        if (\is_array($result)) return new self($result);
        if (\is_string($result)) return Str::factory($result);
        return $result;
    }

    public function replaceRecurisve(array ...$replacements): Arr
    {
        \array_unshift($replacements, $this->items);
        return new self(\call_user_func_array('\\array_replace_recursive', $replacements));
    }

    public function replace(array ...$replacements): Arr
    {
        \array_unshift($replacements, $this->items);
        return new self(\call_user_func_array('\\array_replace', $replacements));
    }

    public function reserve(bool $preserve_keys = false): Arr
    {
        return new self(\array_reverse($this->items, $preserve_keys));
    }

    public function search(mixed $needle, bool $strict = false): string|int|bool
    {
        return \array_search($needle, $this->items, $strict);
    }

    public function shift(): mixed
    {
        return \array_shift($this->items);
    }

    public function slice(int $offset, int $length = null, bool $preserve_keys = false): Arr
    {
        return new self(\array_slice($this->items, $offset, $length, $preserve_keys));
    }

    public function skip(int $count): Arr
    {
        return $this->slice($count);
    }

    public function take(int $count): Arr
    {
        if ($count < 0) {
            return $this->slice($count, \abs($count));
        }
        return $this->slice(0, $count);
    }

    public function splice(int $offset, int $length = null, mixed $replacement = []): Arr
    {
        if ($length === null) {
            $length = \count($this->items);
        }
        return new self(\array_splice($this->items, $offset, $length, $replacement));
    }

    public function sum(): int|float
    {
        return \array_sum($this->items);
    }

    public function average(): int|float
    {
        return \array_sum($this->items) / \count($this->items);
    }

    public function median(): int|float
    {
        $array = $this->items;
        \sort($array);
        $length = \count($array);
        $middle = $length / 2.0;
        return $length % 2 === 0 ? ($array[\floor($middle) - 1] + $array[\ceil($middle)]) / 2 : $array[\intval($middle)];
    }

    public function uDiffAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_udiff_assoc', $arrays));
    }

    public function uDiffUAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        if (!\is_callable(\array_reverse(\array_values($arrays))[1])) {
            throw new \RuntimeException('The second last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_udiff_uassoc', $arrays));
    }

    public function uDiff(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_udiff', $arrays));
    }

    public function uIntersectAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_uintersect_assoc', $arrays));
    }

    public function uIntersectUAssoc(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        if (!\is_callable(\array_reverse(\array_values($arrays))[1])) {
            throw new \RuntimeException('The second last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_uintersect_uassoc', $arrays));
    }

    public function uIntersect(array $array2, array ...$arrays): Arr
    {
        \array_unshift($arrays, $this->items);
        \array_unshift($arrays, $array2);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        return new self(\call_user_func_array('\\array_uintersect', $arrays));
    }

    public function unique(int $sort_flags = SORT_STRING): Arr
    {
        return new self(\array_unique($this->items, $sort_flags));
    }

    public function unshift(mixed $value): int
    {
        return \array_unshift($this->items, $value);
    }

    public function values(): Arr
    {
        return new self(\array_values($this->items));
    }

    public function walkRecursive(callable $callback, mixed $userdata = null): Arr
    {
        $items = $this->items;
        \array_walk_recursive($items, $callback, $userdata);
        return new self($items);
    }

    public function walk(callable $callback, mixed $userdata = null): Arr
    {
        $items = $this->items;
        \array_walk($items, $callback, $userdata);
        return new self($items);
    }

    public function sort(callable $callback = null): Arr
    {
        $items = $this->items;
        $callback && \is_callable($callback) ? \uasort($items, $callback) : \asort($items, $callback ?? SORT_REGULAR);
        return new self($items);
    }

    public function sortDesc(int $sort_flags = SORT_REGULAR): Arr
    {
        $items = $this->items;
        \arsort($items, $sort_flags);
        return new self($items);
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function inArray(mixed $needle, bool $strict = false): bool
    {
        return \in_array($needle, $this->items, $strict);
    }

    public function sortKeys(int $sort_flags = SORT_REGULAR, bool $descending = false): Arr
    {
        $items = $this->items;
        $descending ? \krsort($items, $sort_flags) : \ksort($items, $sort_flags);
        return new self($items);
    }

    public function natcasesort(): Arr
    {
        $items = $this->items;
        \natcasesort($items);
        return new self($items);
    }

    public function natsort(): Arr
    {
        $items = $this->items;
        \natsort($items);
        return new self($items);
    }

    public function shuffle(): Arr
    {
        $items = $this->items;
        \shuffle($items);
        return new self($items);
    }

    public function uksort(callable $callback): Arr
    {
        $items = $this->items;
        \uksort($items, $callback);
        return new self($items);
    }

    public function usort(callable $callback): Arr
    {
        $items = $this->items;
        \usort($items, $callback);
        return new self($items);
    }

    public function each(callable $callback): Arr
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function only(array $keys): Arr
    {
        return new self(\array_intersect_key($this->items, \array_flip($keys)));
    }

    public function prepend(mixed $value, string|bool|null|float|int $key = null): Arr
    {
        if ($key === null) {
            \array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }
        return $this;
    }

    public function append(mixed $value, string|bool|null|float|int $key = null): Arr
    {
        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items = $this->items + [$key => $value];
        }
        return $this;
    }

    public function divide(): Arr
    {
        return new self([\array_keys($this->items), \array_values($this->items)]);
    }

    public function dot(string $prepend = '', string $delimiter = '.'): Arr
    {
        $results = [];
        foreach ($this->items as $key => $value) {
            if (\is_array($value) && !empty($value)) {
                $results = \array_merge(
                    $results,
                    (new self($value))->dot($prepend . $key . $delimiter)->__toArray()
                );
            } else {
                $results[$prepend . $key] = $value;
            }
        }
        return new self($results);
    }

    public function implode(string $separator): Str
    {
        return Str::factory(\implode($separator, $this->items));
    }

    public function httpBuildQuery(string $numeric_prefix = null, string $arg_separator = null, int $encoding_type = PHP_QUERY_RFC1738): Str
    {
        return Str::httpBuildQuery($this->items, $numeric_prefix, $arg_separator, $encoding_type);
    }

    public function forget(string|array $keys, string $delimiter = '.'): Arr
    {
        $keys = (array)$keys;
        if (\count($keys) === 0) {
            return new self();
        }
        $items = $this->items;
        foreach ($keys as $key) {
            if (\array_key_exists($key, $items)) {
                unset($items[$key]);
                continue;
            }

            $parts = \explode($delimiter, $key);
            $array = &$items;
            while (\count($parts) > 1) {
                $part = \array_shift($parts);
                if (\array_key_exists($part, $array) && \is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }
            unset($array[\array_shift($parts)]);
        }
        return new self($items);
    }

    public function except(array $keys): Arr
    {
        return $this->forget($keys);
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->items);
    }

    public function get(string $key = null, string $delimiter = '.'): mixed
    {
        if ($key === null) {
            return $this->items;
        }
        if (\array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }
        $keys = \str_contains($key, $delimiter) ? \explode($delimiter, $key) : [$key];
        $result = &$this->items;
        foreach ($keys as $key) {
            if (\is_array($result) && \array_key_exists($key, $result)) {
                $result = &$result[$key];
            } else {
                return null;
            }
        }
        return $result;
    }

    public function groupBy(\Closure $closure): Arr
    {
        throw new \RuntimeException('Currently not supported!'); // @TODO
    }

    public function set(string $key, mixed $value, string $delimiter = '.'): static
    {
        $keys = \str_contains($key, $delimiter) ? \explode($delimiter, $key) : [$key];
        $item = &$this->items;
        foreach ($keys as $key) {
            $item = \is_array($item) ? $item : [];
            if (!\array_key_exists($key, $item) || !\is_array($item[$key])) {
                $item[$key] = [];
            }
            $item = &$item[$key];
        }
        $item = $value;
        return $this;
    }

    public function has(string|int $key = null, string $delimiter = '.'): bool
    {
        if (\array_key_exists($key, $this->items)) {
            return true;
        }
        $keys = \str_contains($key, $delimiter) ? \explode($delimiter, $key) : [$key];
        $items = &$this->items;
        foreach ($keys as $key) {
            if (\is_array($items) && \array_key_exists($key, $items)) {
                $items = &$items[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    public function union(array $items): Arr
    {
        return new self($this->items + $items);
    }

    public function nth(int $step, int $offset = 0): Arr
    {
        $new = [];
        $position = 0;
        foreach ($this->items as $item) {
            if ($position % $step === $offset) {
                $new[] = $item;
            }
            $position++;
        }
        return new self($new);
    }

    public function pluck(string $value, string|null $key = null, string $delimiter = '.'): Arr
    {
        $result = [];
        foreach ($this->items as $item) {
            $value = \data_get($item, $value, $delimiter);
            if ($key === null) {
                $result[] = $value;
            } else {
                $result[\data_get($item, $key, $delimiter)] = $value;
            }
        }
        return new self($result);
    }

}
