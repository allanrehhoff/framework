<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Arr::class)]
class ArrTest extends TestCase {
	public function testGet() {
		$this->assertEquals('default', \Arr::get(null, 'key', 'default'));
		$this->assertEquals('value', \Arr::get(['key' => 'value'], 'key', 'default'));
		$this->assertEquals('default', \Arr::get(['key' => 'value'], 'missing', 'default'));
	}

	public function testHas() {
		$this->assertFalse(\Arr::has(null, 'key'));
		$this->assertTrue(\Arr::has(['key' => 'value'], 'key'));
		$this->assertFalse(\Arr::has(['key' => 'value'], 'missing'));
	}

	public function testSet() {
		$array = null;
		\Arr::set($array, 'key', 'value');
		$this->assertEquals(['key' => 'value'], $array);

		\Arr::set($array, 'another', 'value');
		$this->assertEquals(['key' => 'value', 'another' => 'value'], $array);
	}

	public function setForget() {
		$array = ['key' => 'value', 'another' => 'value'];
		\Arr::forget($array, 'key');
		$this->assertEquals(['another' => 'value'], $array);

		\Arr::forget($array, 'missing');
		$this->assertEquals(['another' => 'value'], $array);
	}

	public function testEmpty() {
		$this->assertTrue(\Arr::isEmpty(null));
		$this->assertTrue(\Arr::isEmpty([]));
		$this->assertFalse(\Arr::isEmpty(['key' => 'value']));
	}

	public function testFlatten() {
		$this->assertEquals([], \Arr::flatten(null));
		$this->assertEquals([1, 2, 3], \Arr::flatten([1, [2, 3]]));
		$this->assertEquals([1, 2, 3, 4], \Arr::flatten([1, [2, [3, 4]]]));
	}

	public function testSlice() {
		$this->assertEquals([], \Arr::slice(null, 0, 2));
		$this->assertEquals([2, 3], \Arr::slice([1, 2, 3, 4], 1, 2));
		$this->assertEquals([3, 4], \Arr::slice([1, 2, 3, 4], 2));
	}

	public function testMerge() {
		$this->assertEquals([], \Arr::merge(null, null));
		$this->assertEquals([1, 2, 3], \Arr::merge(null, [1, 2], [3]));
		$this->assertEquals([1, 2, 3, 4], \Arr::merge([1, 2], null, [3, 4]));
	}

	public function testFilter() {
		$this->assertEquals([], \Arr::filter(null, fn($v) => $v > 1));
		$this->assertEquals([2, 3], \Arr::filter([1, 2, 3], fn($v) => $v > 1));
		$this->assertEquals(['key' => 'value'], \Arr::filter(['key' => 'value', 'other' => null], fn($v) => !is_null($v)));
	}

	public function testMap() {
		$this->assertEquals([], \Arr::map(null, fn($v) => $v * 2));
		$this->assertEquals([2, 4, 6], \Arr::map([1, 2, 3], fn($v) => $v * 2));
		$this->assertEquals(['VALUE'], \Arr::map(['value'], 'strtoupper'));
	}

	/**
	 * Test the safe method of the Arr class.
	 *
	 * @return void
	 */
	public function testSafe() {
		// Test case: basic array with strings
		$array = [
			'name' => '<b>John</b>',
			'city' => 'New York'
		];
		$escapedArray = \Arr::safe($array);
		$this->assertEquals([
			'name' => '&lt;b&gt;John&lt;/b&gt;',
			'city' => 'New York'
		], $escapedArray);

		// Test case: nested array
		$array = [
			'person' => [
				'name' => '<i>Alice</i>',
				'age' => 30
			],
			'location' => 'Paris'
		];
		$escapedArray = \Arr::safe($array);
		$this->assertEquals([
			'person' => [
				'name' => '&lt;i&gt;Alice&lt;/i&gt;',
				'age' => 30
			],
			'location' => 'Paris'
		], $escapedArray);

		// Test case: mixed types (string, array, and integer)
		$array = [
			'name' => '<script>alert("xss")</script>',
			'age' => 25,
			'children' => ['Tom', 'Jerry']
		];
		$escapedArray = \Arr::safe($array);
		$this->assertEquals([
			'name' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
			'age' => 25,
			'children' => ['Tom', 'Jerry']
		], $escapedArray);

		// Test case: empty array
		$array = [];
		$escapedArray = \Arr::safe($array);
		$this->assertEquals([], $escapedArray);
	}
}
