<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use Temkaa\Collections\Enum\SortOrder;
use Temkaa\Collections\Exception\FieldNotFoundException;
use Temkaa\Collections\Filter\All;
use Temkaa\Collections\Filter\AndX;
use Temkaa\Collections\Filter\Equals;
use Temkaa\Collections\Filter\FilterInterface;
use Temkaa\Collections\Filter\Greater;
use Temkaa\Collections\Filter\Less;
use Temkaa\Collections\Filter\None;
use Temkaa\Collections\Filter\Not;
use Temkaa\Collections\Filter\OrX;
use Temkaa\Collections\Model\Sort;
use Tests\Stub\ArrayAccessClass;
use Tests\Stub\ClassWithProperty2;
use Throwable;
use function sprintf;

abstract class AbstractCollectionTestCase extends TestCase
{
    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForAddElementTest(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3, 3], 3, null];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;
        $el3 = new stdClass();
        $el3->test = 2;

        yield [[$el1, $el2], [$el1, $el2, $el3], $el3, null];

        yield [['a' => 1], ['a' => 1, 'b' => 1], 1, 'b'];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForChunkTest(): iterable
    {
        yield [[1, 2, 3], [[1], [2], [3]], 1];
        yield [[1, 2, 3], [[1, 2], [3]], 2];
        yield [[1, 2, 3], [[1, 2, 3]], 3];
        yield [['a' => 1, 'b' => 1, 'c' => 1], [['a' => 1, 'b' => 1], ['c' => 1]], 2];
        yield [[1 => 1, 10 => 2, 11 => 3], [[1 => 1, 10 => 2], [11 => 3]], 2];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForCountTest(): iterable
    {
        yield [[1, 2, 3], 3];
        yield [[], 0];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForEmptyTest(): iterable
    {
        yield [[1, 2, 3], false];
        yield [[], true];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForFilterTest(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], new All()];
        yield [[1, 2, 3], [], new None()];
        yield [[1, 2, 3], [1, 2, 3], new Not(new None())];
        yield [[1, 2, 3], [1], Equals::value(1)];
        yield [[1, 2, 3], [2, 3], new Not(Equals::value(1))];
        yield [[1, 2, 3], [3], Greater::value(2)];
        yield [[1, 2, 3], [1, 2], new Not(Greater::value(2))];
        yield [[1, 2, 3], [2, 3], new Not(Less::value(2))];
        yield [[1, 2, 3], [1, 2, 3], new OrX([Equals::value(1), Equals::value(2), Equals::value(3)])];
        yield [[1, 2, 3], [], new Not(new OrX([Equals::value(1), Equals::value(2), Equals::value(3)]))];
        yield [[1, 2, 3], [], new AndX([Equals::value(1), Equals::value(2), Equals::value(3)])];
        yield [[1, 2, 3], [1], new AndX([Equals::value(1), Less::value(2)])];
        yield [[1, 2, 3], [2, 3], new Not(new AndX([Equals::value(1), Less::value(2)]))];
        yield [
            [1, 2, 3, 4, 5, 6],
            [],
            new AndX([
                new OrX([Equals::value(1), Equals::value(2)]),
                new OrX([Equals::value(3), Equals::value(4)]),
                new AndX([Greater::value(4), Less::value(10)]),
            ]),
        ];
        yield [
            [1, 2, 3, 4, 5, 6],
            [1],
            new AndX([
                new OrX([Equals::value(1), Equals::value(2)]),
                Equals::value(1),
                new Not(new AndX([Greater::value(4), Less::value(10)])),
            ]),
        ];
        yield [
            [1, 2, 3, 4, 5, 6],
            [1, 2, 3, 4],
            new OrX([
                new AndX([Greater::value(0), Equals::value(2)]),
                Equals::value(1),
                new Not(new AndX([Greater::value(4), Less::value(10)])),
            ]),
        ];

        $cases = [
            [
                'path'   => 'prop',
                'values' => [
                    new ClassWithProperty2(1),
                    new ClassWithProperty2(2),
                    new ClassWithProperty2(3),
                    new ClassWithProperty2(4),
                    new ClassWithProperty2(5),
                    new ClassWithProperty2(6),
                ],
            ],
            [
                'path'   => 'prop.prop.prop',
                'values' => [
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(1))),
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(2))),
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(3))),
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(4))),
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(5))),
                    new ClassWithProperty2(new ClassWithProperty2(new ClassWithProperty2(6))),
                ],
            ],
            [
                'path'   => 'prop.0.prop.1.prop',
                'values' => [
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(1)]))],
                    ),
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(2)]))],
                    ),
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(3)]))],
                    ),
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(4)]))],
                    ),
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(5)]))],
                    ),
                    new ClassWithProperty2(
                        [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(6)]))],
                    ),
                ],
            ],
            [
                'path'   => 'prop2',
                'values' => [
                    ['prop2' => 1],
                    ['prop2' => 2],
                    ['prop2' => 3],
                    ['prop2' => 4],
                    ['prop2' => 5],
                    ['prop2' => 6],
                ],
            ],
            [
                'path'   => 'prop.prop2.prop3.other.value',
                'values' => [
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 1]]]]],
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 2]]]]],
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 3]]]]],
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 4]]]]],
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 5]]]]],
                    ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 6]]]]],
                ],
            ],
            [
                'path'   => 'prop.0.prop3.1.value',
                'values' => [
                    ['prop' => [['prop3' => [[], ['value' => 1]]]]],
                    ['prop' => [['prop3' => [[], ['value' => 2]]]]],
                    ['prop' => [['prop3' => [[], ['value' => 3]]]]],
                    ['prop' => [['prop3' => [[], ['value' => 4]]]]],
                    ['prop' => [['prop3' => [[], ['value' => 5]]]]],
                    ['prop' => [['prop3' => [[], ['value' => 6]]]]],
                ],
            ],
        ];

        foreach ($cases as $case) {
            $path = $case['path'];
            [$value1, $value2, $value3, $value4, $value5, $value6] = $case['values'];

            yield [[$value1, $value2, $value3], [$value1, $value2, $value3], new All()];
            yield [[$value1, $value2, $value3], [], new None()];
            yield [[$value1, $value2, $value3], [$value1, $value2, $value3], new Not(new None())];
            yield [[$value1, $value2, $value3], [$value1], Equals::path(path: $path, value: 1)];
            yield [[$value1, $value2, $value3], [$value2, $value3], new Not(Equals::path(path: $path, value: 1))];
            yield [[$value1, $value2, $value3], [$value3], Greater::path(path: $path, value: 2)];
            yield [[$value1, $value2, $value3], [$value1, $value2], new Not(Greater::path(path: $path, value: 2))];
            yield [[$value1, $value2, $value3], [$value2, $value3], new Not(Less::path(path: $path, value: 2))];
            yield [
                [$value1, $value2, $value3],
                [$value1, $value2, $value3],
                new OrX(
                    [
                        Equals::path(path: $path, value: 1),
                        Equals::path(path: $path, value: 2),
                        Equals::path(path: $path, value: 3),
                    ],
                ),
            ];
            yield [
                [$value1, $value2, $value3],
                [],
                new Not(
                    new OrX([
                        Equals::path(path: $path, value: 1),
                        Equals::path(path: $path, value: 2),
                        Equals::path(path: $path, value: 3),
                    ]),
                ),
            ];
            yield [
                [$value1, $value2, $value3],
                [],
                new AndX([
                    Equals::path(path: $path, value: 1),
                    Equals::path(path: $path, value: 2),
                    Equals::path(path: $path, value: 3),
                ]),
            ];
            yield [
                [$value1, $value2, $value3],
                [$value1],
                new AndX([Equals::path(path: $path, value: 1), Less::path(path: $path, value: 2)]),
            ];
            yield [
                [$value1, $value2, $value3],
                [$value2, $value3],
                new Not(new AndX([Equals::path(path: $path, value: 1), Less::path(path: $path, value: 2)])),
            ];
            yield [
                [$value1, $value2, $value3, $value4, $value5, $value6],
                [],
                new AndX([
                    new OrX([Equals::path(path: $path, value: 1), Equals::path(path: $path, value: 2)]),
                    new OrX([Equals::path(path: $path, value: 3), Equals::path(path: $path, value: 4)]),
                    new AndX([Greater::path(path: $path, value: 4), Less::path(path: $path, value: 10)]),
                ]),
            ];
            yield [
                [$value1, $value2, $value3, $value4, $value5, $value6],
                [$value1],
                new AndX([
                    new OrX([Equals::path(path: $path, value: 1), Equals::path(path: $path, value: 2)]),
                    Equals::path(path: $path, value: 1),
                    new Not(new AndX([Greater::path(path: $path, value: 4), Less::path(path: $path, value: 10)])),
                ]),
            ];
            yield [
                [$value1, $value2, $value3, $value4, $value5, $value6],
                [$value1, $value2, $value3, $value4],
                new OrX([
                    new AndX([Greater::path(path: $path, value: 0), Equals::path(path: $path, value: 2)]),
                    Equals::path(path: $path, value: 1),
                    new Not(new AndX([Greater::path(path: $path, value: 4), Less::path(path: $path, value: 10)])),
                ]),
            ];
        }

        $path = 'prop.prop2.prop3.other.value';
        yield [
            [
                'key1' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 1]]]]],
                'key2' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 2]]]]],
                'key3' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 3]]]]],
                'key4' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 4]]]]],
                'key5' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 5]]]]],
                'key6' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 6]]]]],
            ],
            [
                'key1' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 1]]]]],
                'key2' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 2]]]]],
                'key3' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 3]]]]],
                'key4' => ['prop' => ['prop2' => ['prop3' => ['other' => ['value' => 4]]]]],
            ],
            new OrX([
                new AndX([Greater::path(path: $path, value: 0), Equals::path(path: $path, value: 2)]),
                Equals::path(path: $path, value: 1),
                new Not(new AndX([Greater::path(path: $path, value: 4), Less::path(path: $path, value: 10)])),
            ]),
        ];

        $path = 'prop.0.prop.1.prop';
        $values = [
            'key1' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(1)]))],
            ),
            'key2' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(2)]))],
            ),
            'key3' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(3)]))],
            ),
            'key4' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(4)]))],
            ),
            'key5' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(5)]))],
            ),
            'key6' => new ClassWithProperty2(
                [new ClassWithProperty2(new ArrayAccessClass([[], new ClassWithProperty2(6)]))],
            ),
        ];
        yield [
            $values,
            [
                'key1' => $values['key1'],
                'key5' => $values['key5'],
            ],
            new OrX([
                Equals::path(path: $path, value: 1),
                Equals::path(path: $path, value: 5),
            ]),
        ];
    }

    /**
     * @return iterable<array{0: array<int|string, mixed>, 1: FilterInterface, 2: class-string<Throwable>, 3: string}>
     */
    public static function getDataForFilterWithIncorrectPropertyAccessTest(): iterable
    {
        yield [
            [new stdClass()],
            Equals::path(path: 'non_existing_property', value: 1),
            FieldNotFoundException::class,
            sprintf('Property "non_existing_property" was not found in object "%s".', stdClass::class),
        ];
        yield [
            [new stdClass()],
            Equals::path(path: '0', value: 1),
            FieldNotFoundException::class,
            sprintf(
                'Property "0" was not found in object "%s". Maybe you forgot to implement \ArrayAccess?',
                stdClass::class,
            ),
        ];
        yield [
            [['a' => 1]],
            Equals::path(path: 'b', value: 1),
            FieldNotFoundException::class,
            'Key array{a: int}[b] does not exist.',
        ];
        yield [
            [['a' => ['b' => 1]]],
            Equals::path(path: 'b', value: 1),
            FieldNotFoundException::class,
            'Key array{a: array}[b] does not exist.',
        ];
        yield [
            [new ArrayAccessClass(['a'])],
            Equals::path(path: '1', value: 1),
            FieldNotFoundException::class,
            sprintf('Key [1] does not exist in ArrayAccess object "%s".', ArrayAccessClass::class),
        ];
        yield [
            [new ArrayAccessClass(['a' => 'b'])],
            Equals::path(path: 'path', value: 1),
            FieldNotFoundException::class,
            sprintf('Key [path] does not exist in ArrayAccess object "%s".', ArrayAccessClass::class),
        ];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForFirstTestElement(): iterable
    {
        yield [[1, 2, 3], 1];
        yield [[], null];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForHasElementTest(): iterable
    {
        yield [[1, 2, 3], 1, true];
        yield [[1, 2, 3], 4, false];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;
        $el3 = new stdClass();
        $el3->test = 2;

        yield [[$el1, $el2], $el3, false];
        yield [[$el1, $el2, $el3], $el3, true];

        yield [['a' => 1], 1, true];
        yield [['a' => 1], 'b', false];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForHasKeyTest(): iterable
    {
        yield [[1, 2, 3], 1, true];
        yield [[1, 2, 3], 3, false];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;
        $el3 = new stdClass();
        $el3->test = 2;

        yield [[$el1, $el2], 0, true];
        yield [[0 => $el1, 2 => $el2], 1, false];
        yield [[$el1, $el2, $el3], 3, false];

        yield [['a' => 1], 'a', true];
        yield [['a' => 1], 'b', false];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForIsNotEmptyTest(): iterable
    {
        yield [[1, 2, 3], true];
        yield [[], false];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForLastElementTest(): iterable
    {
        yield [[1, 2, 3], 3];
        yield [['a' => 'b', 'c' => 'd'], 'd'];
        yield [[], null];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForLastKeyTest(): iterable
    {
        yield [[1, 2, 3], 2];
        yield [['a' => 'b', 'c' => 'd'], 'c'];
        yield [[], null];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForMapTest(): iterable
    {
        yield [[1, 2, 3], [2, 4, 6], static fn (int $element): int => $element * 2];
        yield [[[1, 2], [3, 4]], [[1, 2, 0], [3, 4, 0]], static fn (array $element): array => [...$element, 0]];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;

        yield [[$el1, $el2], [[$el1], [$el2]], static fn (object $element): array => [$element]];
        yield [
            ['a' => 1, 'b' => 2, 'c' => 3],
            ['a' => '2a', 'b' => '4b', 'c' => '6c'],
            static fn (int $element, string $key): string => sprintf('%s%s', $element * 2, $key),
        ];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForMergeTest(): iterable
    {
        yield [[1, 2, 3], [3], [1, 2, 3, 3], false];
        yield [['a' => 1], ['b' => 1], ['a' => 1, 'b' => 1], false];
        yield [['a' => 1], [1, 2], ['a' => 1, 1, 2], false];
        yield [['a' => 1], ['a' => 1], ['a' => 1], false];
        yield [['a' => 1], ['a' => 2], ['a' => [1, 2]], true];
        yield [['a' => 1], ['a' => [2, 3, 4, [1]]], ['a' => [1, 2, 3, 4, [1]]], true];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForRemoveElementTest(): iterable
    {
        yield [[1, 2, 3], [1, 2], 3];
        yield [[0 => 1, 4 => 2], [1], 2];
        yield [[1, 2, 3], [1, 3], 2];
        yield [[1, 2, 3], [1, 2, 3], 4];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;
        $el3 = new stdClass();
        $el3->test = 2;

        yield [[$el1, $el2], [$el1], $el2];
        yield [[$el1, $el2], [$el1, $el2], $el3];

        yield [['a' => 1, 'b' => 2], ['b' => 2], 1];
        yield [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2], 3];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForRemoveKeyTest(): iterable
    {
        yield [[1, 2, 3], [1, 2], 2];
        yield [[0 => 1, 4 => 2], [1], 4];
        yield [[1, 2, 3], [1, 3], 1];
        yield [[1, 2, 3], [1, 2, 3], 4];

        $el1 = new stdClass();
        $el1->test = 1;
        $el2 = new stdClass();
        $el2->test = 2;

        yield [[$el1, $el2], [$el1], 1];
        yield [[$el1, $el2], [$el1, $el2], 2];

        yield [['a' => 1, 'b' => 2], ['b' => 2], 'a'];
        yield [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2], 'c'];
    }

    /**
     * @return iterable<array<int|string, mixed>>
     */
    public static function getDataForSliceTest(): iterable
    {
        yield [[1, 2, 3], [3], 2, 1];
        yield [[1, 2, 3], [3], -1, 1];
        yield [[1, 2, 3, 4, 5], [4, 5], -2, 2];
        yield [[1, 2, 3], [2, 3], 1, null];

        yield [['a' => 10, 'b' => 9, 'c' => 8], ['a' => 10, 'b' => 9], 0, 2];
        yield [['a' => 10, 'b' => 9, 'c' => 8], ['b' => 9, 'c' => 8], 1, null];
        yield [['a' => 10, 'b' => 9, 'c' => 8, 'd' => 100], ['c' => 8, 'd' => 100], -2, 2];
    }

    /**
     * @return iterable<array{0: array<int|string, mixed>, 1: array<int|string, mixed>, 2: Sort}>
     */
    public static function getDataForSortTest(): iterable
    {
        yield [
            [1, 2, 3, 4],
            [4, 3, 2, 1],
            Sort::value(SortOrder::Desc),
        ];
        yield [
            [1, 2, 3, 4],
            [1, 2, 3, 4],
            Sort::value(SortOrder::Asc),
        ];

        yield [
            ['a', 'b', 'c', 'd'],
            ['a', 'b', 'c', 'd'],
            Sort::value(SortOrder::Asc),
        ];
        yield [
            ['a', 'b', 'c', 'd'],
            ['d', 'c', 'b', 'a'],
            Sort::value(SortOrder::Desc),
        ];

        yield [
            ['a' => 13, 'b' => 12, 'c' => 11, 'd' => 10],
            ['d' => 10, 'c' => 11, 'b' => 12, 'a' => 13],
            Sort::value(SortOrder::Asc),
        ];
        yield [
            ['a' => 11, 'b' => 12, 'c' => 13, 'd' => 14],
            ['d' => 14, 'c' => 13, 'b' => 12, 'a' => 11],
            Sort::value(SortOrder::Desc),
        ];

        $value1 = new ClassWithProperty2(new ClassWithProperty2(1));
        $value2 = new ClassWithProperty2(new ClassWithProperty2(2));
        $value3 = new ClassWithProperty2(new ClassWithProperty2(3));
        yield [
            [$value1, $value2, $value3],
            [$value3, $value2, $value1],
            Sort::path(['prop.prop' => SortOrder::Desc]),
        ];
        yield [
            [$value3, $value2, $value1],
            [$value1, $value2, $value3],
            Sort::path(['prop.prop' => SortOrder::Asc]),
        ];

        yield [
            ['a' => $value1, 'b' => $value2, 'c' => $value3],
            ['c' => $value3, 'b' => $value2, 'a' => $value1],
            Sort::path(['prop.prop' => SortOrder::Desc]),
        ];
        yield [
            ['a' => $value3, 'b' => $value2, 'c' => $value1],
            ['c' => $value1, 'b' => $value2, 'a' => $value3],
            Sort::path(['prop.prop' => SortOrder::Asc]),
        ];

        $value1 = ['a' => ['b' => 1]];
        $value2 = ['a' => ['b' => 2]];
        $value3 = ['a' => ['b' => 3]];
        yield [
            [$value1, $value2, $value3],
            [$value3, $value2, $value1],
            Sort::path(['a.b' => SortOrder::Desc]),
        ];
        yield [
            [$value3, $value2, $value1],
            [$value1, $value2, $value3],
            Sort::path(['a.b' => SortOrder::Asc]),
        ];

        yield [
            ['a' => $value1, 'b' => $value2, 'c' => $value3],
            ['c' => $value3, 'b' => $value2, 'a' => $value1],
            Sort::path(['a.b' => SortOrder::Desc]),
        ];
        yield [
            ['a' => $value3, 'b' => $value2, 'c' => $value1],
            ['c' => $value1, 'b' => $value2, 'a' => $value3],
            Sort::path(['a.b' => SortOrder::Asc]),
        ];

        $value1 = ['a' => ['b' => 1, 'c' => 1]];
        $value2 = ['a' => ['b' => 1, 'c' => 2]];
        $value3 = ['a' => ['b' => 2, 'c' => 1]];
        $value4 = ['a' => ['b' => 2, 'c' => 2]];
        $value5 = ['a' => ['b' => 3, 'c' => 1]];
        yield [
            [$value1, $value3, $value2, $value5, $value4],
            [$value5, $value3, $value4, $value1, $value2],
            Sort::path(['a.b' => SortOrder::Desc, 'a.c' => SortOrder::Asc]),
        ];
        yield [
            [$value5, $value4, $value3, $value2, $value1],
            [$value1, $value2, $value3, $value4, $value5],
            Sort::path(['a.b' => SortOrder::Asc, 'a.c' => SortOrder::Asc]),
        ];

        yield [
            ['a' => $value1, 'b' => $value3, 'c' => $value2, 'd' => $value5, 'e' => $value4],
            ['d' => $value5, 'b' => $value3, 'e' => $value4, 'a' => $value1, 'c' => $value2],
            Sort::path(['a.b' => SortOrder::Desc, 'a.c' => SortOrder::Asc]),
        ];
        yield [
            ['a' => $value5, 'b' => $value4, 'c' => $value3, 'd' => $value2, 'e' => $value1],
            ['e' => $value1, 'd' => $value2, 'c' => $value3, 'b' => $value4, 'a' => $value5],
            Sort::path(['a.b' => SortOrder::Asc, 'a.c' => SortOrder::Asc]),
        ];
    }

    /**
     * @return iterable<array{0: array<int|string, mixed>, 1: array<int|string, mixed>, 2:list<string>|string|null}>
     */
    public static function getDataForUniqueTest(): iterable
    {
        yield [
            [1, 2, 3, 3, 3],
            [1, 2, 3],
            null,
        ];
        yield [
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 3, 'e' => 3],
            ['a' => 1, 'b' => 2, 'c' => 3],
            null,
        ];
        yield [
            [1, 3, 3, 2, 6],
            [1, 3, 2, 6],
            null,
        ];

        $value1 = new ClassWithProperty2(new ClassWithProperty2('a'));
        $value2 = new ClassWithProperty2(new ClassWithProperty2('b'));
        $value3 = new ClassWithProperty2(new ClassWithProperty2('a'));
        $value4 = new ClassWithProperty2(new ClassWithProperty2('c'));
        $value5 = new ClassWithProperty2(new ClassWithProperty2(1));
        $value6 = new ClassWithProperty2(new ClassWithProperty2(null));
        yield [
            [$value1, $value2, $value3, $value4, $value5, $value6],
            [$value1, $value2, $value4, $value5, $value6],
            'prop.prop',
        ];
        yield [
            [$value1, $value2, $value3, $value4, $value5, $value6],
            [$value1, $value2, $value4, $value5, $value6],
            ['prop', 'prop'],
        ];
        yield [
            ['a' => $value1, 'b' => $value2, 'c' => $value3, 'd' => $value4, 'e' => $value5, 'f' => $value6],
            ['a' => $value1, 'b' => $value2, 'd' => $value4, 'e' => $value5, 'f' => $value6],
            ['prop', 'prop'],
        ];

        $value1 = ['prop' => ['prop' => 'a']];
        $value2 = ['prop' => ['prop' => 'b']];
        $value3 = ['prop' => ['prop' => 'a']];
        $value4 = ['prop' => ['prop' => 'c']];
        $value5 = ['prop' => ['prop' => 1]];
        $value6 = ['prop' => ['prop' => null]];
        yield [
            [$value1, $value2, $value3, $value4, $value5, $value6],
            [$value1, $value2, $value4, $value5, $value6],
            'prop.prop',
        ];
        yield [
            [$value1, $value2, $value3, $value4, $value5, $value6],
            [$value1, $value2, $value4, $value5, $value6],
            ['prop', 'prop'],
        ];
        yield [
            ['a' => $value1, 'b' => $value2, 'c' => $value3, 'd' => $value4, 'e' => $value5, 'f' => $value6],
            ['a' => $value1, 'b' => $value2, 'd' => $value4, 'e' => $value5, 'f' => $value6],
            ['prop', 'prop'],
        ];
    }
}
