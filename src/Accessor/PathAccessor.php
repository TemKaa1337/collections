<?php

declare(strict_types=1);

namespace Temkaa\Collections\Accessor;

use ArrayAccess;
use ReflectionException;
use ReflectionProperty;
use Temkaa\Collections\Exception\FieldNotFoundException;
use function array_filter;
use function array_key_exists;
use function array_shift;
use function array_values;
use function explode;
use function is_array;
use function is_numeric;
use function is_object;

/**
 * @internal
 */
final readonly class PathAccessor
{
    /**
     * @param non-empty-list<string>|non-empty-string $path
     */
    public function get(mixed $source, array|string $path): mixed
    {
        $path = array_values(
            array_filter(
                is_array($path)
                    ? $path
                    : explode('.', $path),
                static fn (mixed $element): bool => $element !== '',
            ),
        );

        return $this->doGet($source, $path);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param list<string|numeric-string> $path
     */
    private function doGet(mixed $source, array $path): mixed
    {
        if (!$path) {
            return $source;
        }

        $currentPath = array_shift($path);

        if (is_array($source) || $source instanceof ArrayAccess) {
            $formattedPath = is_numeric($currentPath) ? (int) $currentPath : $currentPath;

            $keyExists = is_array($source) ? array_key_exists($currentPath, $source) : isset($source[$currentPath]);
            if (!$keyExists) {
                throw FieldNotFoundException::key($source, $currentPath);
            }

            $value = $source[$formattedPath];

            return $value !== null ? $this->doGet($value, $path) : null;
        }

        if (!is_object($source)) {
            return $source;
        }

        try {
            $property = new ReflectionProperty($source, $currentPath);

            $propertyValue = $property->isInitialized($source) ? $property->getValue($source) : null;

            return $propertyValue !== null ? $this->doGet($propertyValue, $path) : null;
        } catch (ReflectionException) {
            throw FieldNotFoundException::property($source, $currentPath);
        }
    }
}
