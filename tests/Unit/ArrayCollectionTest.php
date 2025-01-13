<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Temkaa\Collections\ArrayCollection;
use Temkaa\Collections\CollectionInterface;
use Temkaa\Collections\Filter\FilterInterface;
use Temkaa\Collections\Model\Sort;
use Throwable;
use function array_map;

final class ArrayCollectionTest extends AbstractCollectionTestCase
{
    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForAddElementTest')]
    public function testAddElement(
        array $sourceElements,
        array $resultElements,
        mixed $element,
        int|string|null $key,
    ): void {
        $collection = new ArrayCollection($sourceElements);
        $collection->addElement($element, $key);

        self::assertSame($resultElements, $collection->toArray());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     * @param positive-int             $size
     */
    #[DataProvider('getDataForChunkTest')]
    public function testChunk(array $sourceElements, array $resultElements, int $size): void
    {
        $chunks = array_map(
            static fn (CollectionInterface $collection): array => $collection->toArray(),
            (new ArrayCollection($sourceElements))->chunk($size),
        );

        self::assertSame($resultElements, $chunks);
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForCountTest')]
    public function testCount(array $elements, int $expectedCount): void
    {
        self::assertSame($expectedCount, (new ArrayCollection($elements))->count());
    }

    public function testEach(): void
    {
        $eachCallback = static function (object $element, int $key): bool {
            if ($key === 2) {
                return false;
            }

            /** @phpstan-ignore property.notFound */
            $element->value *= 2;

            return true;
        };

        /** @phpstan-ignore property.notFound */
        $mapCallback = static fn (object $element): int => $element->value;

        $el1 = new stdClass();
        $el1->value = 1;
        $el2 = new stdClass();
        $el2->value = 2;
        $el3 = new stdClass();
        $el3->value = 3;
        $el4 = new stdClass();
        $el4->value = 4;

        $result = (new ArrayCollection([$el1, $el2, $el3, $el4]))
            ->each($eachCallback)
            ->map($mapCallback)
            ->toArray();

        self::assertSame(
            [2, 4, 3, 8],
            $result,
        );
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForEmptyTest')]
    public function testEmpty(array $elements, bool $expectedResult): void
    {
        self::assertSame($expectedResult, (new ArrayCollection($elements))->empty());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForFilterTest')]
    public function testFilter(array $sourceElements, array $resultElements, FilterInterface $filter): void
    {
        self::assertSame($resultElements, (new ArrayCollection($sourceElements))->filter($filter)->toArray());
    }

    /**
     * @param array<int|string, mixed> $elements
     * @param class-string<Throwable>  $exceptionClass
     */
    #[DataProvider('getDataForFilterWithIncorrectPropertyAccessTest')]
    public function testFilterWithIncorrectPropertyAccess(
        array $elements,
        FilterInterface $filter,
        string $exceptionClass,
        string $exceptionMessage,
    ): void {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($exceptionMessage);

        (new ArrayCollection($elements))->filter($filter);
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForFirstTestElement')]
    public function testFirstElement(array $elements, ?int $expectedValue): void
    {
        self::assertSame($expectedValue, (new ArrayCollection($elements))->firstElement());
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForHasElementTest')]
    public function testHasElement(array $elements, mixed $element, bool $has): void
    {
        self::assertSame($has, (new ArrayCollection($elements))->hasElement($element));
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForHasKeyTest')]
    public function testHasKey(array $elements, int|string $key, bool $has): void
    {
        self::assertSame($has, (new ArrayCollection($elements))->hasKey($key));
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForLastElementTest')]
    public function testLastElement(array $elements, mixed $expectedValue): void
    {
        self::assertSame($expectedValue, (new ArrayCollection($elements))->lastElement());
    }

    /**
     * @param array<int|string, mixed> $elements
     */
    #[DataProvider('getDataForLastKeyTest')]
    public function testLastKey(array $elements, mixed $expectedValue): void
    {
        self::assertSame($expectedValue, (new ArrayCollection($elements))->lastKey());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForMapTest')]
    public function testMap(array $sourceElements, array $resultElements, callable $callback): void
    {
        self::assertSame($resultElements, (new ArrayCollection($sourceElements))->map($callback)->toArray());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $mergeElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForMergeTest')]
    public function testMerge(
        array $sourceElements,
        array $mergeElements,
        array $resultElements,
        bool $isRecursive,
    ): void {
        $collection = new ArrayCollection($sourceElements);
        $resultCollection = $collection->merge(new ArrayCollection($mergeElements), $isRecursive);

        self::assertSame(
            $resultElements,
            $resultCollection->toArray(),
        );
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForRemoveElementTest')]
    public function testRemoveElement(array $sourceElements, array $resultElements, mixed $element): void
    {
        $collection = new ArrayCollection($sourceElements);
        $collection->removeElement($element);

        self::assertSame($resultElements, $collection->toArray());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForRemoveKeyTest')]
    public function testRemoveKey(array $sourceElements, array $resultElements, int|string $key): void
    {
        $collection = new ArrayCollection($sourceElements);
        $collection->removeKey($key);

        self::assertSame($resultElements, $collection->toArray());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForSliceTest')]
    public function testSlice(array $sourceElements, array $resultElements, int $offset, ?int $length): void
    {
        self::assertSame($resultElements, (new ArrayCollection($sourceElements))->slice($offset, $length)->toArray());
    }

    /**
     * @param array<int|string, mixed> $sourceElements
     * @param array<int|string, mixed> $resultElements
     */
    #[DataProvider('getDataForSortTest')]
    public function testSort(array $sourceElements, array $resultElements, Sort $sort): void
    {
        self::assertSame($resultElements, (new ArrayCollection($sourceElements))->sort($sort)->toArray());
    }

    public function testToArray(): void
    {
        $elements = [1, 2, 3];
        self::assertSame($elements, (new ArrayCollection($elements))->toArray());

        $elements = ['a' => 1, 2, 3];
        self::assertSame($elements, (new ArrayCollection($elements))->toArray());
    }

    /**
     * @param array<int|string, mixed>                     $sourceElements
     * @param array<int|string, mixed>                     $resultElements
     * @param non-empty-list<string>|non-empty-string|null $path
     */
    #[DataProvider('getDataForUniqueTest')]
    public function testUnique(array $sourceElements, array $resultElements, array|string|null $path): void
    {
        self::assertSame($resultElements, (new ArrayCollection($sourceElements))->unique($path)->toArray());
    }
}
