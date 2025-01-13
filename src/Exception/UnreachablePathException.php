<?php

declare(strict_types=1);

namespace Temkaa\Collections\Exception;

use InvalidArgumentException;
use Temkaa\Collections\Model\Path;
use function get_debug_type;
use function implode;
use function is_string;
use function sprintf;

/**
 * @api
 */
final class UnreachablePathException extends InvalidArgumentException implements CollectionExceptionInterface
{
    public function __construct(mixed $value, Path $path)
    {
        parent::__construct(
            sprintf(
                'Cannot access path "%s" on value of type "%s".',
                is_string($path->path) ? $path->path : implode(', ', $path->path),
                get_debug_type($value),
            ),
        );
    }
}
