<?php

use PHPUnit\Framework\TestCase;

/**
 * Class StrTest
 */
class StrTest extends TestCase
{
	/**
	 * Test case for contains method (case-sensitive).
	 */
	public function testContains(): void {
		$this->assertTrue(\Str::contains('Hello, World!', 'World'));
		$this->assertFalse(\Str::contains('Hello, World!', 'world'));
		$this->assertFalse(\Str::contains('Hello, World!', null));
		$this->assertFalse(\Str::contains(null, 'World'));
	}

	/**
	 * Test case for containsIgnoreCase method (case-insensitive).
	 */
	public function testContainsIgnoreCase(): void {
		$this->assertTrue(\Str::containsIgnoreCase('Hello, World!', 'world'));
		$this->assertFalse(\Str::containsIgnoreCase('Hello, World!', 'foo'));
		$this->assertFalse(\Str::containsIgnoreCase(null, 'World'));
		$this->assertFalse(\Str::containsIgnoreCase('Hello, World!', null));
	}

	/**
	 * Test case for startsWith method (case-sensitive).
	 */
	public function testStartsWith(): void {
		$this->assertTrue(\Str::startsWith('Hello, World!', 'Hello'));
		$this->assertFalse(\Str::startsWith('Hello, World!', 'world'));
		$this->assertFalse(\Str::startsWith(null, 'Hello'));
		$this->assertFalse(\Str::startsWith('Hello, World!', null));
	}

	/**
	 * Test case for startsWithIgnoreCase method (case-insensitive).
	 */
	public function testStartsWithIgnoreCase(): void {
		$this->assertTrue(Str::startsWithIgnoreCase('Hello, World!', 'hello'));
		$this->assertFalse(\Str::startsWithIgnoreCase('Hello, World!', 'foo'));
		$this->assertFalse(\Str::startsWithIgnoreCase(null, 'Hello'));
		$this->assertFalse(\Str::startsWithIgnoreCase('Hello, World!', null));
	}

	/**
	 * Test case for endsWith method (case-sensitive).
	 */
	public function testEndsWith(): void {
		$this->assertTrue(\Str::endsWith('Hello, World!', 'World!'));
		$this->assertFalse(\Str::endsWith('Hello, World!', 'world!'));
		$this->assertFalse(\Str::endsWith(null, 'World!'));
		$this->assertFalse(\Str::endsWith('Hello, World!', null));
	}

	/**
	 * Test case for endsWithIgnoreCase method (case-insensitive).
	 */
	public function testEndsWithIgnoreCase(): void {
		$this->assertTrue(\Str::endsWithIgnoreCase('Hello, World!', 'world!'));
		$this->assertFalse(\Str::endsWithIgnoreCase('Hello, World!', 'foo'));
		$this->assertFalse(\Str::endsWithIgnoreCase(null, 'World!'));
		$this->assertFalse(\Str::endsWithIgnoreCase('Hello, World!', null));
	}

	/**
	 * Test case for match method (regular expression match).
	 */
	public function testTest(): void {
		$this->assertTrue(\Str::test('/^Hello/', 'Hello, World!'));
		$this->assertFalse(\Str::test('/foo/', 'Hello, World!'));
		$this->assertFalse(\Str::test('/World/', null));
	}

	/**
	 * Test the matchAll method for matching all occurrences of a pattern.
	 */
	public function testMatch(): void {
		$this->assertSame(['123-456-789', '123', '456', '789'], \Str::match('/(\d+)-(\d+)-(\d+)/', '123-456-789'));
	}

	/**
	 * Test matchAll method with a valid pattern and subject.
	 */
	public function testMatchAll(): void {
		$this->assertSame(['20', '5', '100'], \Str::matchAll('/\d+/', 'The price is $20 and the quantity is 5. The total is $100.'));
		$this->assertEmpty(\Str::matchAll('/[a-z]+/', "1234567890"));
		$this->assertEmpty(\Str::matchAll(null, null));
		$this->assertEmpty(\Str::matchAll('/\w+/', null));
	}

	/**
	 * Test the ascii method for replacing Unicode characters with their ASCII counterparts.
	 */
	public function testAscii(): void {
		$input = 'héllø wörld';
		$expectedOutput = 'hello world';

		$this->assertEquals($expectedOutput, \Str::ascii($input));
	}
}
