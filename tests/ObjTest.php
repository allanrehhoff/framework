<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Obj::class)]
class ObjTest extends TestCase {
    public function testGet(): void {
        $object = (object) ['name' => 'John', 'age' => 30];

        $this->assertSame('John', Obj::get($object, 'name'));
        $this->assertNull(Obj::get($object, 'address'));
        $this->assertSame('Unknown', Obj::get($object, 'address', 'Unknown'));
    }

    public function testSet(): void {
        $object = new \stdClass;

        Obj::set($object, 'name', 'Jane');
        $this->assertObjectHasProperty('name', $object);
        $this->assertSame('Jane', $object->name);
    }

    public function testSafe(): void {
        $object = new \stdClass();
        $object->name = '<b>John</b>';
        $object->description = 'This is a description with "quotes" and & symbols.';

        $escapedObject = Obj::safe($object);
        $this->assertSame('&lt;b&gt;John&lt;/b&gt;', $escapedObject->name);
        $this->assertSame('This is a description with &quot;quotes&quot; and &amp; symbols.', $escapedObject->description);

        $this->assertEquals(new \stdClass, Obj::safe(null));
    }

    public function testHas(): void {
        $object = (object) ['name' => 'John', 'age' => 30];

        $this->assertTrue(Obj::has($object, 'name'));
        $this->assertFalse(Obj::has($object, 'address'));
        $this->assertFalse(Obj::has(null, 'name'));
    }

    public function testToArray(): void {
        $object = (object) ['name' => 'John', 'age' => 30];

        $this->assertSame(['name' => 'John', 'age' => 30], Obj::toArray($object));
        $this->assertSame([], Obj::toArray(null));
    }

    public function testToJson(): void {
        $object = (object) ['name' => 'John', 'age' => 30];

        $this->assertSame('{"name":"John","age":30}', Obj::toJson($object));
        $this->assertNull(Obj::toJson(null));
    }
}
