<?php

declare(strict_types=1);

namespace Temkaa\Collections\Exception;

use ArrayAccess;
use InvalidArgumentException;
use function array_keys;
use function array_map;
use function implode;
use function is_array;
use function is_numeric;
use function sprintf;

/**
 * @api
 */
final class FieldNotFoundException extends InvalidArgumentException implements CollectionExceptionInterface
{
    /**
     * @param array<int|string, mixed>|ArrayAccess<int|string, mixed> $array
     */
    public static function key(array|ArrayAccess $array, int|string $key): self
    {
        if (is_array($array)) {
            $message = sprintf(
                'Key array{%s}[%s] does not exist.',
                implode(
                    ', ',
                    array_map(
                        static fn (int|string $key, mixed $value): string => sprintf(
                            '%s: %s',
                            $key,
                            get_debug_type($value),
                        ),
                        array_keys($array),
                        $array,
                    ),
                ),
                $key,
            );
        } else {
            $message = sprintf(
                'Key [%s] does not exist in ArrayAccess object "%s".',
                $key,
                get_debug_type($array),
            );
        }

        return new self($message);
    }

    public static function property(object $object, string $property): self
    {
        $message = sprintf('Property "%s" was not found in object "%s".', $property, $object::class);
        if (is_numeric($property)) {
            $message .= ' Maybe you forgot to implement \ArrayAccess?';
        }

        return new self($message);
    }
}
