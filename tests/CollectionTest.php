<?php

use PHPUnit\Framework\TestCase;
use Database\Collection;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase {
    private Collection $emptyCollection;
    private Collection $numberCollection;
    private Collection $objectCollection;

    protected function setUp(): void {
        $this->emptyCollection = new Collection([]);
        $this->numberCollection = new Collection([1, 2, 3, 4, 5]);
        
        $obj1 = new stdClass();
        $obj1->id = 1;
        $obj1->name = 'One';
        
        $obj2 = new stdClass();
        $obj2->id = 2;
        $obj2->name = 'Two';
        
        $this->objectCollection = new Collection([$obj1, $obj2]);
    }

    public function testConstructorAndAll(): void {
        $items = [1, 2, 3];
        $collection = new Collection($items);
        $this->assertEquals($items, $collection->all());
    }

    public function testGetFirst(): void {
        $this->assertNull($this->emptyCollection->getFirst());
        $this->assertEquals(1, $this->numberCollection->getFirst());
    }

    public function testGetLast(): void {
        $this->assertNull($this->emptyCollection->getLast());
        $this->assertEquals(5, $this->numberCollection->getLast());
    }

    public function testGetOne(): void {
        // Test finding a number by value
        $this->assertEquals(3, $this->numberCollection->getOne(
            fn($value) => $value === 3
        ));
        
        // Test finding by key
        $this->assertEquals(2, $this->numberCollection->getOne(
            fn($value, $key) => $key === 1
        ));
        
        // Test with object collection
        $foundObject = $this->objectCollection->getOne(
            fn($obj) => $obj->name === 'Two'
        );
        $this->assertEquals(2, $foundObject->id);
        
        // Test with no match, using default
        $this->assertEquals('not found', $this->numberCollection->getOne(
            fn($value) => $value > 10,
            'not found'
        ));
        
        // Test with no match, no default (should return null)
        $this->assertNull($this->numberCollection->getOne(
            fn($value) => $value > 10
        ));
        
        // Test with empty collection
        $this->assertNull($this->emptyCollection->getOne(
            fn($value) => true
        ));
    }

    public function testCount(): void {
        $this->assertEquals(0, $this->emptyCollection->count());
        $this->assertEquals(5, $this->numberCollection->count());
    }

    public function testGetColumn(): void {
        $names = $this->objectCollection->getColumn('name');
        $this->assertEquals(['One', 'Two'], $names->all());
    }

    public function testIsEmpty(): void {
        $this->assertTrue($this->emptyCollection->isEmpty());
        $this->assertFalse($this->numberCollection->isEmpty());
    }

    public function testArrayAccess(): void {
        $this->numberCollection[5] = 6;
        $this->assertTrue(isset($this->numberCollection[5]));
        $this->assertEquals(6, $this->numberCollection[5]);
        
        unset($this->numberCollection[5]);
        $this->assertFalse(isset($this->numberCollection[5]));
    }

    public function testIterator(): void {
        $sum = 0;
        foreach ($this->numberCollection as $number) {
            $sum += $number;
        }
        $this->assertEquals(15, $sum);
    }

    public function testJsonSerialize(): void {
        $json = json_encode($this->numberCollection);
        $this->assertEquals('[1,2,3,4,5]', $json);
    }

    public function testFilter(): void {
        $evenNumbers = $this->numberCollection->filter(fn($item) => $item % 2 === 0);
        $this->assertEquals([1 => 2, 3 => 4], $evenNumbers->all());
    }

    public function testMap(): void {
        $doubled = $this->numberCollection->map(fn($item) => $item * 2);
        $this->assertEquals([2, 4, 6, 8, 10], $doubled->all());
    }

    public function testReduce(): void {
        $sum = $this->numberCollection->reduce(fn($carry, $item) => $carry + $item, 0);
        $this->assertEquals(15, $sum);
    }

    public function testSlice(): void {
        $slice = $this->numberCollection->slice(1, 3);
        $this->assertEquals([1 => 2, 2 => 3, 3 => 4], $slice->all());
    }

    public function testMerge(): void {
        $other = new Collection([6, 7]);
        $merged = $this->numberCollection->merge($other);
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], $merged->all());
    }

    public function testUnique(): void {
        $collection = new Collection([1, 2, 2, 3, 3, 3]);
        $unique = $collection->unique();
        $this->assertEquals([0 => 1, 1 => 2, 3 => 3], $unique->all());
    }

    public function testForget(): void {
        $collection = new Collection(['a' => 1, 'b' => 2, 'c' => 3]);
        $collection->forget(['a', 'c']);
        $this->assertEquals(['b' => 2], $collection->all());
    }
}