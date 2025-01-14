<?php

declare(strict_types=1);

namespace Tests\Stub;

use ArrayAccess;
use function array_key_exists;

final class ArrayAccessClass implements ArrayAccess
{
    public function __construct(
        private array $array,
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->array);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->array[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->array[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->array[$offset]);
    }
}
