<?php

declare(strict_types=1);

namespace TinyFramework\Helpers;

/**
 * @template TKey of int|string
 * @template TValue of mixed
 * @link https://www.php.net/manual/de/ref.array.php
 */
#[\AllowDynamicProperties]
class Arr implements \ArrayAccess, \Iterator, \Countable
{

    use Macroable;

    protected array $items = [];

    /**
     * @param array<TKey, TValue> $items
     * @return Arr
     */
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

    /**
     * @param array<TKey, TValue> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function clone(): self
    {
        return new self($this->items);
    }

    public function array(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function __toArray(): array
    {
        return $this->items;
    }

    /**
     * @return TValue|mixed|null
     */
    public function __get(string $name): mixed
    {
        return $this->items[$name] ?? null;
    }

    /**
     * @param TKey $name
     * @param TValue $value
     * @return void
     */
    public function __set(string|int $name, mixed $value): void
    {
        $this->items[$name] = $value;
    }

    /**
     * @param TKey $name
     * @return bool
     */
    public function __isset(string|int $name): bool
    {
        return \array_key_exists($name, $this->items);
    }

    /**
     * @param TKey $name
     * @return void
     */
    public function __unset(string|int $name): void
    {
        if (\array_key_exists($name, $this->items)) {
            unset($this->items[$name]);
        }
    }

    /**
     * @param TKey|mixed $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->items);
    }

    /**
     * @return TValue
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param TKey|mixed $offset
     * @param TValue $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items[$offset] = $value;
    }

    /**
     * @param TKey|mixed $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @return TValue
     */
    public function current(): mixed
    {
        return \current($this->items);
    }

    public function next(): void
    {
        \next($this->items);
    }

    /**
     * @return TKey|int|string|null
     */
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

    public function changeKeyCase(int $case = CASE_LOWER): static
    {
        $this->items = \array_change_key_case($this->items, $case);
        return $this;
    }

    public function chunk(int $size, bool $preserve_keys = false): static
    {
        $this->items = \array_chunk($this->items, $size, $preserve_keys);
        return $this;
    }

    public function column(int|string|null $column_key, int|string|null $index_key = null): static
    {
        $this->items = \array_column($this->items, $column_key, $index_key);
        return $this;
    }

    public function combineWithValues(array $values): static
    {
        $this->items = \array_combine($this->items, $values);
        return $this;
    }

    public function combineWithKeys(array $keys): static
    {
        $this->items = \array_combine($keys, $this->items);
        return $this;
    }

    public function countBy(callable $callable = null): static
    {
        /** @var array<mixed, integer> $result */
        $result = [];
        foreach ($this->items as $key => $value) {
            $value = \is_callable($callable) ? $callable($value, $key) : $value;
            $result[$value] ??= 0;
            $result[$value]++;
        }
        $this->items = $result;
        return $this;
    }

    public function countValues(): static
    {
        $this->items = \array_count_values($this->items);
        return $this;
    }

    public function diffAssoc(array $array2, array|callable ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = (array)\call_user_func_array('array_diff_assoc', $arrays);
        return $this;
    }

    public function diffUAssoc(array $array2, array|callable ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = (array)\call_user_func_array('\\array_diff_uassoc', $arrays);
        return $this;
    }

    public function diffKey(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = (array)\call_user_func_array('\\array_diff_key', $arrays);
        return $this;
    }

    public function diffUKey(array $array2, array|callable ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = (array)\call_user_func_array('\\array_diff_ukey', $arrays);
        return $this;
    }

    public function diff(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = (array)\call_user_func_array('\\array_diff', $arrays);
        return $this;
    }

    public function flat(string $delimiter = '.'): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $value = is_array($value) ? self::factory($value) : $value;
            if ($value instanceof self) {
                foreach ($value->flat($delimiter)->items as $k => $v) {
                    $result[$key . $delimiter . $k] = $v;
                }
            } else {
                $result[$key] = $value;
            }
        }
        $this->items = $result;
        return $this;
    }

    public function flatten(int $depth = -1): static
    {
        $depth = $depth === -1 ? PHP_INT_MAX : $depth;
        $result = [];
        foreach ($this->items as $item) {
            $item = $item instanceof Arr ? $item->items : $item;
            if (!\is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1 ? \array_values($item) : self::factory($item)->flatten($depth - 1);
                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }
        $this->items = $result;
        return $this;
    }

    public function filter(callable $callback): static
    {
        $this->items = \array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH);
        return $this;
    }

    /**
     * @return TValue|mixed|null
     */
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

    public function flip(): static
    {
        $this->items = \array_flip($this->items);
        return $this;
    }

    public function intersectAssoc(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = \call_user_func_array('\\array_intersect_assoc', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function intersectUAssoc(array $array2, ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_intersect_uassoc', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function intersectByKeys(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = \call_user_func_array('\\array_intersect_key', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function intersectUKey(array $array2, ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_intersect_ukey', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function intersect(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = \call_user_func_array('\\array_intersect', $arrays);
        return $this;
    }

    /**
     * @param TKey|mixed $key
     * @return bool
     */
    public function keyExists(mixed $key): bool
    {
        return \array_key_exists($key, $this->items);
    }

    /**
     * @return TKey|string|int|null
     */
    public function keyFirst(): string|int|null
    {
        return \array_key_first($this->items);
    }

    /**
     * @return TKey|string|int|null
     */
    public function keyLast(): string|int|null
    {
        return \array_key_last($this->items);
    }

    public function keys(): static
    {
        $this->items = \array_keys($this->items);
        return $this;
    }

    /**
     * @return TValue|mixed|null
     */
    public function last(callable $callback = null): mixed
    {
        if (\is_callable($callback)) {
            return (new self(\array_reverse($this->items, true)))->first($callback);
        }
        return \count($this->items) ? \end($this->items) : null;
    }

    public function map(callable $callback): self
    {
        return new self(\array_map($callback, $this->items));
    }

    public function transform(callable $callback): static
    {
        $this->items = \array_map($callback, $this->items);
        return $this;
    }

    public function mapWithKeys(callable $callback): self
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

    public function mergeRecursive(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = \call_user_func_array('\\array_merge_recursive', $arrays);
        return $this;
    }

    public function merge(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        $this->items = \call_user_func_array('\\array_merge', $arrays);
        return $this;
    }

    public function multisort(
        array ...$args
    ): static {
        \array_unshift($args, $this->items);
        $this->items = &$args[0];
        if (!\call_user_func_array('\\array_multisort', $args)) {
            throw new \RuntimeException('array_multisort returns an error.');
        }
        return $this;
    }

    public function pad(int $size, mixed $value = null): static
    {
        $this->items = \array_pad($this->items, $size, $value);
        return $this;
    }

    /**
     * @return TValue
     */
    public function pop(): mixed
    {
        $result = \array_pop($this->items);
        if (\is_string($result)) {
            return Str::factory($result);
        } elseif (\is_array($result)) {
            return new self($result);
        }
        return $result;
    }

    public function product(): int|float
    {
        return \array_product($this->items);
    }

    public function push(mixed ...$values): static
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }
        return $this;
    }

    public function concat(array $source): static
    {
        foreach ($source as $item) {
            $this->items[] = $item;
        }
        return $this;
    }

    public function random(int $num = 1): Str|Arr|int
    {
        $result = \array_rand($this->items, $num);
        if (\is_string($result)) {
            return Str::factory($result);
        } elseif (\is_array($result)) {
            return new self($result);
        }
        return (int)$result;
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $result = \array_reduce($this->items, $callback, $initial);
        if (\is_array($result)) {
            return new self($result);
        }
        if (\is_string($result)) {
            return Str::factory($result);
        }
        return $result;
    }

    public function replaceRecursive(array ...$replacements): static
    {
        \array_unshift($replacements, $this->items);
        $this->items = \call_user_func_array('\\array_replace_recursive', $replacements);
        return $this;
    }

    public function replace(array ...$replacements): static
    {
        \array_unshift($replacements, $this->items);
        $this->items = \call_user_func_array('\\array_replace', $replacements);
        return $this;
    }

    public function reverse(bool $preserve_keys = false): static
    {
        $this->items = \array_reverse($this->items, $preserve_keys);
        return $this;
    }

    public function search(mixed $needle, bool $strict = false): string|int|bool
    {
        return \array_search($needle, $this->items, $strict);
    }

    /**
     * @return TValue|mixed|self|Str
     */
    public function shift(): mixed
    {
        $result = \array_shift($this->items);
        if (\is_string($result)) {
            return Str::factory($result);
        } elseif (\is_array($result)) {
            return new self($result);
        }
        return $result;
    }

    public function slice(int $offset, int $length = null, bool $preserve_keys = false): static
    {
        $this->items = \array_slice($this->items, $offset, $length, $preserve_keys);
        return $this;
    }

    public function skip(int $count): static
    {
        return $this->slice($count);
    }

    public function take(int $count): static
    {
        if ($count < 0) {
            return $this->slice($count, \abs($count));
        }
        return $this->slice(0, $count);
    }

    public function splice(int $offset, int $length = null, mixed $replacement = []): static
    {
        if ($length === null) {
            $length = \count($this->items);
        }
        $result = \array_splice($this->items, $offset, $length, $replacement);
        $this->items = $result;
        return $this;
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
        $middle = intval($length / 2.0);
        return $length % 2 === 0
            ? ($array[$middle + 1] + $array[$middle]) / 2
            : $array[$middle];
    }

    public function uDiffAssoc(array $array2, array|callable ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_udiff_assoc', $arrays);
        return $this;
    }

    public function uDiffUAssoc(array $array2, array ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        if (!\is_callable(\array_reverse(\array_values($arrays))[1])) {
            throw new \RuntimeException('The second last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_udiff_uassoc', $arrays);
        return $this;
    }

    public function uDiff(array $array2, array|callable...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_udiff', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function uIntersectAssoc(array $array2, ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_uintersect_assoc', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function uIntersectUAssoc(array $array2, ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        if (!\is_callable(\array_reverse(\array_values($arrays))[1])) {
            throw new \RuntimeException('The second last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_uintersect_uassoc', $arrays);
        return $this;
    }

    /**
     * @param array $array2
     * @param array ...$arrays
     * @return $this
     */
    public function uIntersect(array $array2, ...$arrays): static
    {
        \array_unshift($arrays, $array2);
        \array_unshift($arrays, $this->items);
        if (!\is_callable($arrays[\array_key_last($arrays)])) {
            throw new \RuntimeException('The last value must be a callable');
        }
        $this->items = \call_user_func_array('\\array_uintersect', $arrays);
        return $this;
    }

    public function unique(int $sort_flags = SORT_STRING): static
    {
        $this->items = \array_unique($this->items, $sort_flags);
        return $this;
    }

    /**
     * @param TValue $value
     */
    public function unshift(mixed $value): int
    {
        return \array_unshift($this->items, $value);
    }

    public function values(): static
    {
        $this->items = \array_values($this->items);
        return $this;
    }

    public function walkRecursive(callable $callback, mixed $userdata = null): static
    {
        \array_walk_recursive($this->items, $callback, $userdata);
        return $this;
    }

    public function walk(callable $callback, mixed $userdata = null): static
    {
        \array_walk($this->items, $callback, $userdata);
        return $this;
    }

    public function sort(int|callable $callback = null): static
    {
        if ($callback && \is_callable($callback)) {
            \uasort($this->items, $callback);
        } else {
            \asort($this->items, is_null($callback) ? SORT_REGULAR : $callback);
        }
        return $this;
    }

    public function sortDesc(int $sort_flags = SORT_REGULAR): static
    {
        \arsort($this->items, $sort_flags);
        return $this;
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function inArray(mixed $needle, bool $strict = false): bool
    {
        return \in_array($needle, $this->items, $strict);
    }

    /**
     * @alias inArray
     */
    public function contains(mixed $needle, bool $strict = false): bool
    {
        return $this->inArray($needle, $strict);
    }

    public function containsOne(array $needles, bool $strict = false): bool
    {
        if (count($needles) === 0) {
            return true;
        }
        foreach ($needles as $needle) {
            if ($this->inArray($needle, $strict)) {
                return true;
            }
        }
        return false;
    }

    public function containsAll(array $needles, bool $strict = false): bool
    {
        if (count($needles) === 0) {
            return true;
        }
        foreach ($needles as $needle) {
            if (!$this->inArray($needle, $strict)) {
                return false;
            }
        }
        return true;
    }

    public function sortKeys(int $sort_flags = SORT_REGULAR, bool $descending = false): static
    {
        $descending ? \krsort($this->items, $sort_flags) : \ksort($this->items, $sort_flags);
        return $this;
    }

    public function natcasesort(): static
    {
        \natcasesort($this->items);
        return $this;
    }

    public function natsort(): static
    {
        \natsort($this->items);
        return $this;
    }

    public function shuffle(): static
    {
        \shuffle($this->items);
        return $this;
    }

    public function uksort(callable $callback): static
    {
        \uksort($this->items, $callback);
        return $this;
    }

    public function usort(callable $callback): static
    {
        \usort($this->items, $callback);
        return $this;
    }

    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

    public function only(string|array $keys): static
    {
        $keys = is_string($keys) ? [$keys] : $keys;
        $this->items = \array_intersect_key($this->items, \array_flip($keys));
        return $this;
    }

    /**
     * @param TValue $value
     */
    public function prepend(mixed $value, string|bool|null|float|int $key = null): static
    {
        if ($key === null) {
            \array_unshift($this->items, $value);
        } else {
            $this->items = [$key => $value] + $this->items;
        }
        return $this;
    }

    /**
     * @param TValue $value
     */
    public function append(mixed $value, string|bool|null|float|int $key = null): static
    {
        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
        return $this;
    }

    public function divide(): static
    {
        $this->items = [\array_keys($this->items), \array_values($this->items)];
        return $this;
    }

    public function dot(string $prepend = '', string $delimiter = '.'): static
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
        $this->items = $results;
        return $this;
    }

    public function undot(string $delimiter = '.'): static
    {
        if (empty($delimiter)) {
            throw new \InvalidArgumentException('Parameter #1 $delimiter must be a non-empty-string.');
        }
        $results = [];
        foreach ($this->items as $itemKey => &$itemValue) {
            if (str_contains($itemKey, $delimiter)) {
                $value = &$results;
                foreach (explode($delimiter, $itemKey) as $keyPart) {
                    if (!array_key_exists($keyPart, $value)) {
                        $value[$keyPart] = [];
                    }
                    if (!is_array($value[$keyPart])) {
                        $value[$keyPart] = [];
                    }
                    $value = &$value[$keyPart];
                }
                $value = $itemValue;
                continue;
            }
            $results[$itemKey] = &$itemValue;
        }
        $this->items = $results;
        return $this;
    }

    public function implode(string $separator): Str
    {
        return Str::factory(\implode($separator, $this->items));
    }

    public function httpBuildQuery(
        string $numeric_prefix = '',
        string $arg_separator = null,
        int $encoding_type = PHP_QUERY_RFC1738
    ): Str {
        return Str::httpBuildQuery($this->items, $numeric_prefix, $arg_separator, $encoding_type);
    }

    public function forget(string|array $keys, string $delimiter = '.'): static
    {
        $keys = (array)$keys;
        if (\count($keys) === 0) {
            return $this;
        }

        assert(!empty($delimiter), 'Parameter #2 $delimiter of function explode expects non-empty-string.');
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
        $this->items = $items;
        return $this;
    }

    public function except(array $keys): static
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

    /**
     * @return array|TValue|mixed|null
     */
    public function get(string $key = null, string $delimiter = '.'): mixed
    {
        if ($key === null) {
            return $this->items;
        }
        if (\array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }
        assert(!empty($delimiter), 'Parameter #2 $delimiter of function explode expects non-empty-string.');
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

    public function groupBy(callable $callable): static
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $groupBy = $callable($value, $key);
            $result[$groupBy] ??= [];
            $result[$groupBy][$key] = $value;
        }
        $this->items = $result;
        return $this;
    }

    /**
     * @param TValue $value
     */
    public function set(string $key, mixed $value, string $delimiter = '.'): static
    {
        assert(!empty($delimiter), 'Parameter #3 $delimiter of function explode expects non-empty-string.');
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
        assert(!empty($delimiter), 'Parameter #2 $delimiter of function explode expects non-empty-string.');
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

    public function union(array|Arr $items): static
    {
        $items = $items instanceof Arr ? $items->toArray() : $items;
        $this->items = $this->items + $items;
        return $this;
    }

    public function nth(int $step, int $offset = 0): static
    {
        $new = [];
        $position = 0;
        if ($step < 2) {
            throw new \InvalidArgumentException('Step size must be greater then 1.');
        }
        if ($offset >= $step) {
            throw new \InvalidArgumentException('Offset size must be slower then step size.');
        }
        foreach ($this->items as $item) {
            if ($position % $step === $offset) {
                $new[] = $item;
            }
            $position++;
        }
        $this->items = $new;
        return $this;
    }

    public function pluck(string $value, string|null $key = null, string $delimiter = '.'): static
    {
        $result = [];
        foreach ($this->items as $item) {
            $val = \data_get($item, $value, $delimiter);
            if ($key === null) {
                $result[] = $val;
            } else {
                $result[\data_get($item, $key, $delimiter)] = $val;
            }
        }
        $this->items = $result;
        return $this;
    }
}
