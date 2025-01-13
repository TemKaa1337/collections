<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

/**
 * @api
 */
final readonly class None implements FilterInterface
{
    public function accept(FilterVisitorInterface $visitor): mixed
    {
        return $visitor->none($this);
    }
}
