Simple Collections
===

# This project offers a collection that provide convenient methods to manipulate your data.

## Installation:
```composer
composer require temkaa/collections
```

## Quickstart
```php
<?php declare(strict_types = 1);

use Temkaa\Collections\ArrayCollection;
use Temkaa\Collections\Model\Sort;
use Temkaa\Collections\Enum\SortOrder;
use Temkaa\Collections\Filter\AndX;
use Temkaa\Collections\Filter\Greater;
use Temkaa\Collections\Filter\Less;

class SomeClass
{
    public function someArrayFunction(): void
    {
        $products = [
            ['id' => 2, 'name' => 'milk'],
            ['id' => 6, 'name' => 'bread'],
            ['id' => 1, 'name' => 'meat'],
            ['id' => 2, 'name' => 'juice'],
        ];

        var_dump(
            (new ArrayCollection($products))->sort(Sort::path(['name' => SortOrder::Asc, 'id' => SortOrder::Desc]))->toArray(),
            (new ArrayCollection($products))
                ->filter(
                    new AndX(
                        [
                            Greater::path(path: 'id', value: 1),
                            Less::path(path: 'id', value: 10),
                        ]
                    )
                )
                ->toArray(),
        );
    }

    public function someObjectFunction(): void
    {
        $result = Database::all() // Some database query
        
        var_dump(
            (new ArrayCollection($products))->unique(path: 'someField.0.value')->toArray(),
            (new ArrayCollection($products))
                ->map(static fn (object $element): int => $elment->getId())
                ->toArray(),
        );
    }
}
```  
## Functionality
### addElement(mixed $value, mixed $key = null): self
Adds a new element to collection.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection([]);
$collection->addElement(value: 'value');
// or
$collection = new ArrayCollection([]);
$collection->addElement(value: 'value', key: 'key');
```
### chunk(int $size): list<CollectionInterface<int|string, mixed>>
Chunks elements in collection by given size.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$chunks = $collection->chunk(1);
```
### count(): int
Returns count of elements in Collection.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$count = $collection->count();
```
### each(callable $callback): CollectionInterface
Executes provided callback on each collection element. If false is returned from callback, iteration stops.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection([$object1, $object2]);
$collection->each(static function (object $element): bool {
    if ($element->getId() === 1) {
        return false;
    }
    
    $element->setValue(10);
    
    return true;
});
```
### filter(FilterInterface $filter): CollectionInterface
Filters the collection with provided callback.
```php
use Temkaa\Collections\ArrayCollection;
use Temkaa\Collections\Filter\Greater;

$collection = new ArrayCollection([$object1, $object2]);
$newCollection = $collection->filter(Greater::path('property'). value: 10)->toArray();
```
### firstElement(): mixed
Returns first element from collection, `null` if collection is empty. 
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$first = $collection->firstElement();
```
### firstKey(): mixed
Returns first key from collection, `null` if collection is empty.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$first = $collection->firstKey();
```
### hasElement(mixed $value): bool
Returns true if element is found, false otherwise.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$exists = $collection->hasElement('element1'); // true
// or
$collection = new ArrayCollection(['key' => 'value']);
$exists = $collection->hasElement('value'); // true
```
### hasKey(mixed $key): bool
Returns true if key is found, false otherwise.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$exists = $collection->hasKey(0); // true
// or
$collection = new ArrayCollection(['key' => 'value']);
$exists = $collection->hasKey('key'); // true
```
### empty(): bool
Returns true if collection is empty, false otherwise.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$isEmpty = $collection->empty(); // false
```
### lastElement(): mixed
Returns last element of collection, null if collection is empty.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$last = $collection->lastElement();
```
### lastKey(): mixed
Returns last key of collection, null if collection is empty.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$last = $collection->lastKey();
```
### map(callable $callback): Collection
Creates new collection from provided callback.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$mappedArray = $collection
    ->map(static fn (string $element): string => $element.'1')
    ->toArray(); // ['element11', 'element21']
```
### merge(CollectionInterface $collection, bool $recursive = false): CollectionInterface
Merges two collections with each other.
```php
use Temkaa\Collections\ArrayCollection;

$collection1 = new ArrayCollection(['element1', 'element2']);
$collection2 = new ArrayCollection(['element3', 'element4']);
$resultArray = $collection1
    ->merge($collection2)
    ->toArray(); // ['element1', 'element2', 'element3', 'element4']
// or
$collection1 = new ArrayCollection(['a' => 'element1', 'b' => 'element2']);
$collection2 = new ArrayCollection(['a' => 'element3', 'b' => 'element4']);
$resultArray = $collection1
    ->merge($collection2, recursive: true)
    ->toArray(); // ['a' => ['element1', 'element3'], 'b' => ['element2', 'element4']]
```
### removeElement(mixed $value): self
Removes provided element from collection or silently does nothing if element does not exist.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$collection->removeElement('element1');
// or
$collection = new ArrayCollection(['a' => 'element1', 'b' => 'element2']);
$collection->removeElement('element1');
```
### removeKey(mixed $key): self
Removes provided key from collection or silently does nothing if key does not exist.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2']);
$collection->removeKey('element1');
// or
$collection = new ArrayCollection(['a' => 'element1', 'b' => 'element2']);
$collection->removeKey('element1');
```
### slice(int $offset, ?int $length = null): CollectionInterface
Slices collection from given offset with provided length. If length is not defined - gets all elements from given offset.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2', 'element3']);
$slice = $collection->slice(offset: 1)->toArray(); // ['element2', 'element3']
// or
$collection = new ArrayCollection(['element1', 'element2', 'element3']);
$slice = $collection->slice(offset: 1, length: 1)->toArray(); // ['element2']
```
### toArray(): array
Returns collection elements.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection(['element1', 'element2', 'element3']);
$array = $collection->toArray(); // ['element1', 'element2', 'element3']
```
### sort(Sort $sort): CollectionInterface
Sorts collection by provided directions by path or just values if array is an array of scalars.
```php
use Temkaa\Collections\ArrayCollection;
use Temkaa\Collections\Enum\SortOrder;
use Temkaa\Collections\Model\Sort;

$object1->setId(1);
$object2->setId(2);
$object3->setId(3);

$collection = new ArrayCollection([$object3, $object2, $object1]);
$sorted = $collection
    ->sort(Sort::path(directions: ['id' => SortOrder::DESC]))
    ->toArray(); // [$object3, $object2, $object1]
// or
$collection = new ArrayCollection([1, 2, 3, 4]);
$sorted = $collection
    ->sort(Sort::value(SortOrder::Desc))
    ->toArray(); // [4, 3, 2, 1]
```
### unique(array|string|null $path = null): CollectionInterface
Returns unique elements by provided path or just unique array by values.
```php
use Temkaa\Collections\ArrayCollection;

$collection = new ArrayCollection([1, 2]);
$unique = $collection->unique()->toArray(); // [1]

$collection = new ArrayCollection([['a' => 1], ['a' => 1]]);
$unique = $collection->unique(path: 'a')->toArray(); // ['a' => 1]
```
