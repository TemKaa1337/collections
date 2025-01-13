<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

/**
 * @api
 */
final readonly class Not implements FilterInterface
{
    public function __construct(
        public FilterInterface $filter,
    ) {
    }

    public function accept(FilterVisitorInterface $visitor): mixed
    {
        return $visitor->not($this);
    }
}
