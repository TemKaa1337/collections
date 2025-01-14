<?php

declare(strict_types=1);

namespace Temkaa\Collections\Model;

use Temkaa\Collections\Enum\SortOrder;

/**
 * @api
 */
final readonly class Sort
{
    /**
     * @param non-empty-array<non-empty-string, SortOrder> $directions
     */
    public static function path(array $directions): self
    {
        return new self($directions);
    }

    public static function value(SortOrder $order): self
    {
        return new self($order);
    }

    /**
     * @param SortOrder|non-empty-array<non-empty-string, SortOrder> $directions
     */
    private function __construct(
        public SortOrder|array $directions,
    ) {
    }
}
