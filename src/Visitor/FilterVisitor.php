<?php

declare(strict_types=1);

namespace Temkaa\Collections\Visitor;

use Closure;
use Temkaa\Collections\Accessor\PathAccessor;
use Temkaa\Collections\Exception\UnreachablePathException;
use Temkaa\Collections\Filter\All;
use Temkaa\Collections\Filter\AndX;
use Temkaa\Collections\Filter\Equals;
use Temkaa\Collections\Filter\FilterVisitorInterface;
use Temkaa\Collections\Filter\Greater;
use Temkaa\Collections\Filter\Less;
use Temkaa\Collections\Filter\None;
use Temkaa\Collections\Filter\Not;
use Temkaa\Collections\Filter\OrX;
use Temkaa\Collections\Model\Path;
use function is_array;
use function is_object;

/**
 * @internal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @template-implements FilterVisitorInterface<Closure(mixed): bool>
 */
final readonly class FilterVisitor implements FilterVisitorInterface
{
    public function __construct(
        private PathAccessor $pathAccessor,
    ) {
    }

    public function all(All $filter): Closure
    {
        return static fn (): true => true;
    }

    public function and(AndX $filter): Closure
    {
        return function (mixed $value) use ($filter): bool {
            foreach ($filter->filters as $subFilter) {
                if (!$subFilter->accept($this)($value)) {
                    return false;
                }
            }

            return true;
        };
    }

    public function equals(Equals $filter): Closure
    {
        return fn (mixed $value): bool => $this->getValue($value, $filter->left) === $filter->right;
    }

    public function greater(Greater $filter): Closure
    {
        return fn (mixed $value): bool => $this->getValue($value, $filter->left) > $filter->right;
    }

    public function less(Less $filter): Closure
    {
        return fn (mixed $value): bool => $this->getValue($value, $filter->left) < $filter->right;
    }

    public function none(None $filter): Closure
    {
        return static fn (): false => false;
    }

    public function not(Not $filter): Closure
    {
        return fn (mixed $value): bool => !$filter->filter->accept($this)($value);
    }

    public function or(OrX $filter): Closure
    {
        return function (mixed $value) use ($filter): bool {
            foreach ($filter->filters as $subFilter) {
                if ($subFilter->accept($this)($value)) {
                    return true;
                }
            }

            return false;
        };
    }

    private function getValue(mixed $source, ?Path $path): mixed
    {
        if (!$path) {
            return $source;
        }

        if (!is_object($source) && !is_array($source)) {
            throw new UnreachablePathException($source, $path);
        }

        return $this->pathAccessor->get($source, $path->path);
    }
}
