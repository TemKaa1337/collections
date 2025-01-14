<?php

declare(strict_types=1);

namespace Temkaa\Collections;

use ArrayIterator;
use Closure;
use Temkaa\Collections\Accessor\PathAccessor;
use Temkaa\Collections\Enum\SortOrder;
use Temkaa\Collections\Filter\FilterInterface;
use Temkaa\Collections\Filter\FilterVisitorInterface;
use Temkaa\Collections\Model\Sort;
use Temkaa\Collections\Visitor\FilterVisitor;
use function array_chunk;
use function array_filter;
use function array_is_list;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_merge;
use function array_merge_recursive;
use function array_search;
use function array_slice;
use function array_unique;
use function array_values;
use function count;
use function in_array;
use function uasort;
use function usort;

/**
 * @api
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @template TKey of array-key
 * @template TValue of mixed
 *
 * @template-implements CollectionInterface<TKey, TValue>
 */
final class ArrayCollection implements CollectionInterface
{
    /**
     * @var FilterVisitorInterface<Closure(mixed): bool>
     */
    private readonly FilterVisitorInterface $filterVisitor;

    private readonly PathAccessor $pathAccessor;

    /**
     * @param array<TKey, TValue> $elements
     */
    public function __construct(private array $elements = [])
    {
        $this->pathAccessor = new PathAccessor();
        $this->filterVisitor = new FilterVisitor($this->pathAccessor);
    }

    /**
     * @param TValue    $value
     * @param TKey|null $key
     *
     * @return self<TKey, TValue>
     */
    public function addElement(mixed $value, mixed $key = null): self
    {
        if ($key === null) {
            $this->elements[] = $value;
        } else {
            $this->elements[$key] = $value;
        }

        return $this;
    }

    /**
     * @param positive-int $size
     *
     * @return list<self<TKey, TValue>>
     */
    public function chunk(int $size): array
    {
        $result = [];

        $chunks = array_chunk($this->elements, $size, preserve_keys: !array_is_list($this->elements));
        foreach ($chunks as $chunk) {
            $result[] = new self($chunk);
        }

        return $result;
    }

    /**
     * @return non-negative-int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * @param callable(TValue, TKey): void $callback
     *
     * @return self<TKey, TValue>
     */
    public function each(callable $callback): self
    {
        foreach ($this->elements as $key => $value) {
            $callback($value, $key);
        }

        return $this;
    }

    public function empty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function filter(FilterInterface $filter): self
    {
        $isList = array_is_list($this->elements);

        $result = array_filter($this->elements, $filter->accept($this->filterVisitor));

        return new self($isList ? array_values($result) : $result);
    }

    /**
     * @return TValue|null
     */
    public function firstElement(): mixed
    {
        $firstKey = array_key_first($this->elements);

        return $firstKey === null ? null : $this->elements[$firstKey];
    }

    /**
     * @return TKey|null
     */
    public function firstKey(): int|string|null
    {
        return array_key_first($this->elements);
    }

    /**
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * @param TValue $value
     */
    public function hasElement(mixed $value): bool
    {
        return in_array($value, $this->elements, true);
    }

    /**
     * @param int|string $key
     */
    public function hasKey(mixed $key): bool
    {
        return array_key_exists($key, $this->elements);
    }

    /**
     * @return TValue|null
     */
    public function lastElement(): mixed
    {
        $latestKey = array_key_last($this->elements);

        return $latestKey === null ? null : $this->elements[$latestKey];
    }

    /**
     * @return TKey|null
     */
    public function lastKey(): int|string|null
    {
        return array_key_last($this->elements);
    }

    /**
     * @template TNewValue of mixed
     *
     * @param callable(TValue $value, TKey $key): TNewValue $callback
     *
     * @return self<TKey, TNewValue>
     */
    public function map(callable $callback): self
    {
        $collection = [];

        foreach ($this->elements as $key => $value) {
            $collection[$key] = $callback($value, $key);
        }

        return new self($collection);
    }

    /**
     * @template TNewValue of mixed
     *
     * @param self<TKey, TValue> $collection
     * @param bool               $recursive
     *
     * @return ($recursive is true ? self<TKey, TNewValue> : self<TKey, TValue>)
     */
    public function merge(CollectionInterface $collection, bool $recursive = false): self
    {
        $elements = $recursive
            ? array_merge_recursive($this->elements, $collection->toArray())
            : array_merge($this->elements, $collection->toArray());

        /** @var ($recursive is true ? self<TKey, TNewValue> : self<TKey, TValue>) */
        return new self($elements);
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->elements);
    }

    /**
     * @param TKey $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->elements[$offset] ?? null;
    }

    /**
     * @param TKey $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->elements[$offset] = $value;
    }

    /**
     * @param TKey $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->elements[$offset]);
        }
    }

    /**
     * @param TValue $value
     *
     * @return self<TKey, TValue>
     */
    public function removeElement(mixed $value): self
    {
        if (!$this->hasElement($value)) {
            return $this;
        }

        $elements = $this->elements;

        $isList = array_is_list($elements);

        $key = array_search($value, $elements, true);
        if ($key !== false) {
            unset($this->elements[$key]);

            if ($isList) {
                $this->elements = array_values($this->elements);
            }
        }

        return $this;
    }

    /**
     * @param TKey $key
     *
     * @return self<TKey, TValue>
     */
    public function removeKey(mixed $key): self
    {
        if (!$this->offsetExists($key)) {
            return $this;
        }

        $isList = array_is_list($this->elements);

        $this->offsetUnset($key);

        if ($isList) {
            $this->elements = array_values($this->elements);
        }

        return $this;
    }

    /**
     * @return self<TKey, TValue>
     */
    public function slice(int $offset, ?int $length = null): self
    {
        $preserveKeys = !array_is_list($this->elements);

        return new self(array_slice($this->elements, $offset, $length, $preserveKeys));
    }

    /**
     * @return self<TKey, TValue>
     */
    public function sort(Sort $sort): self
    {
        $elements = $this->elements;

        $isList = array_is_list($elements);

        if ($sort->directions instanceof SortOrder) {
            /**
             * @param TValue $a
             * @param TValue $b
             *
             * @return int<-1, 1>
             */
            $comparator = static function (mixed $a, mixed $b) use ($sort): int {
                $valuesRelation = $a <=> $b;

                if ($valuesRelation === 0) {
                    return 0;
                }

                return match ($sort->directions) {
                    SortOrder::Asc  => $valuesRelation,
                    SortOrder::Desc => -$valuesRelation,
                };
            };
        } else {
            /**
             * @param TValue $a
             * @param TValue $b
             *
             * @return int<-1, 1>
             */
            $comparator = function (mixed $a, mixed $b) use ($sort): int {
                foreach ($sort->directions as $path => $order) {
                    $aValue = $this->pathAccessor->get($a, $path);
                    $bValue = $this->pathAccessor->get($b, $path);

                    $valuesRelation = $aValue <=> $bValue;

                    if ($valuesRelation === 0) {
                        continue;
                    }

                    return match ($order) {
                        SortOrder::Asc  => $valuesRelation,
                        SortOrder::Desc => -$valuesRelation,
                    };
                }

                return 0;
            };
        }

        $isList ? usort($elements, $comparator) : uasort($elements, $comparator);

        return new self($elements);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /**
     * @param non-empty-list<string>|non-empty-string|null $path
     *
     * @return self<TKey, TValue>
     */
    public function unique(array|string|null $path = null): self
    {
        $elements = $this->elements;

        $isList = array_is_list($elements);

        if (!$path) {
            $result = array_unique($elements);

            return new self($isList ? array_values($result) : $result);
        }

        $result = [];
        $duplicates = [];

        foreach ($elements as $key => $value) {
            $extractedValue = $this->pathAccessor->get($value, $path);

            if (!in_array($extractedValue, $duplicates, true)) {
                $duplicates[] = $extractedValue;

                if ($isList) {
                    $result[] = $value;
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return new self($result);
    }
}
