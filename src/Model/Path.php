<?php

declare(strict_types=1);

namespace Temkaa\Collections\Model;

/**
 * @internal
 */
final readonly class Path
{
    /**
     * @param non-empty-list<string>|non-empty-string $path
     */
    public function __construct(
        public array|string $path,
    ) {
    }
}
