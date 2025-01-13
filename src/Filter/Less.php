<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

use Temkaa\Collections\Model\Path;

/**
 * @api
 */
final readonly class Less implements FilterInterface
{
    /**
     * @param non-empty-list<string>|non-empty-string $path
     */
    public static function path(array|string $path, mixed $value): self
    {
        return new self(new Path($path), $value);
    }

    public static function value(mixed $value): self
    {
        return new self(null, $value);
    }

    public function accept(FilterVisitorInterface $visitor): mixed
    {
        return $visitor->less($this);
    }

    private function __construct(
        public ?Path $left,
        public mixed $right,
    ) {
    }
}
