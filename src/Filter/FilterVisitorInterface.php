<?php

declare(strict_types=1);

namespace Temkaa\Collections\Filter;

/**
 * @api
 *
 * @template TResult
 */
interface FilterVisitorInterface
{
    /**
     * @return TResult
     */
    public function all(All $filter): mixed;

    /**
     * @return TResult
     */
    public function and(AndX $filter): mixed;

    /**
     * @return TResult
     */
    public function equals(Equals $filter): mixed;

    /**
     * @return TResult
     */
    public function greater(Greater $filter): mixed;

    /**
     * @return TResult
     */
    public function less(Less $filter): mixed;

    /**
     * @return TResult
     */
    public function none(None $filter): mixed;

    /**
     * @return TResult
     */
    public function not(Not $filter): mixed;

    /**
     * @return TResult
     */
    public function or(OrX $filter): mixed;
}
