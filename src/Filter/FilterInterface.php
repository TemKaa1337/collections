<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

/**
 * @api
 */
interface FilterInterface
{
    /**
     * @template TResult
     *
     * @param FilterVisitorInterface<TResult> $visitor
     *
     * @return TResult
     */
    public function accept(FilterVisitorInterface $visitor): mixed;
}
