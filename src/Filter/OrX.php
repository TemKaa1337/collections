<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

/**
 * @api
 */
final readonly class OrX implements FilterInterface
{
    /**
     * @param non-empty-list<FilterInterface> $filters
     */
    public function __construct(
        public array $filters,
    ) {
    }

    public function accept(FilterVisitorInterface $visitor): mixed
    {
        return $visitor->or($this);
    }
}
