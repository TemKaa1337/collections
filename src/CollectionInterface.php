<?php

declare(strict_types=1);

namespace Temkaa\Collections;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Temkaa\Collections\Filter\FilterInterface;
use Temkaa\Collections\Model\Sort;

/**
 * @api
 *
 * @template TKey
 * @template TValue
 * @template-extends ArrayAccess<TKey, TValue>
 * @template-extends IteratorAggregate<TKey, TValue>
 */
interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param TValue    $value
     * @param TKey|null $key
     *
     * @return self<TKey, TValue>
     */
    public function addElement(mixed $value, mixed $key = null): self;

    /**
     * @param positive-int $size
     *
     * @return list<self<TKey, TValue>>
     */
    public function chunk(int $size): array;

    /**
     * @param callable(TValue, TKey): void $callback
     *
     * @return self<TKey, TValue>
     */
    public function each(callable $callback): self;

    public function empty(): bool;

    /**
     * @return self<TKey, TValue>
     */
    public function filter(FilterInterface $filter): self;

    /**
     * @return TValue
     */
    public function firstElement(): mixed;

    /**
     * @return TKey|null
     */
    public function firstKey(): mixed;

    /**
     * @param TValue $value
     */
    public function hasElement(mixed $value): bool;

    /**
     * @param TKey $key
     */
    public function hasKey(mixed $key): bool;

    /**
     * @return TValue|null
     */
    public function lastElement(): mixed;

    /**
     * @return TKey|null
     */
    public function lastKey(): mixed;

    /**
     * @template TNewValue
     *
     * @param callable(TValue, TKey): TNewValue $callback
     *
     * @return self<TKey, TNewValue>
     */
    public function map(callable $callback): self;

    /**
     * @template TNewValue
     *
     * @param self<TKey, TValue> $collection
     * @param bool               $recursive
     *
     * @return ($recursive is true ? self<TKey, TNewValue> : self<TKey, TValue>)
     */
    public function merge(self $collection, bool $recursive = false): self;

    /**
     * @param TValue $value
     *
     * @return self<TKey, TValue>
     */
    public function removeElement(mixed $value): self;

    /**
     * @param TKey $key
     *
     * @return self<TKey, TValue>
     */
    public function removeKey(mixed $key): self;

    /**
     * @return self<TKey, TValue>
     */
    public function slice(int $offset, ?int $length = null): self;

    /**
     * @return self<TKey, TValue>
     */
    public function sort(Sort $sort): self;

    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    /**
     * @param non-empty-list<string>|non-empty-string|null $path
     *
     * @return self<TKey, TValue>
     */
    public function unique(array|string|null $path = null): self;
}
