<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Str::class)]
class StrTest extends TestCase {
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

		/**
	 * Test encode method.
	 */
	public function testEncode(): void {
		$this->assertSame('SGVsbG8=', Str::encode('Hello'));
		$this->assertSame('', Str::encode(null));
	}

	/**
	 * Test decode method.
	 */
	public function testDecode(): void {
		$this->assertSame('Hello', Str::decode('SGVsbG8='));
		$this->assertSame('', Str::decode(null));
	}

	/**
	 * Test len method.
	 */
	public function testLen(): void {
		$this->assertSame(5, Str::len('Hello'));
		$this->assertSame(0, Str::len(null));
	}

	/**
	 * Test lower method.
	 */
	public function testLower(): void {
		$this->assertSame('hello', Str::lower('Hello'));
		$this->assertSame('', Str::lower(null));
	}

	/**
	 * Test upper method.
	 */
	public function testUpper(): void {
		$this->assertSame('HELLO', Str::upper('Hello'));
		$this->assertSame('', Str::upper(null));
	}

	/**
	 * Test substr method.
	 */
	public function testSubstr(): void {
		$this->assertSame('Hell', Str::substr('Hello', 0, 4));
		$this->assertSame('', Str::substr(null, 0, 4));
	}

	/**
	 * Test normalizeWhitespace method.
	 */
	public function testNormalizeWhitespace(): void {
		$this->assertSame('Hello World', Str::normalizeWhitespace("Hello    World"));
		$this->assertSame('', Str::normalizeWhitespace(null));
	}

	/**
	 * Test slug method.
	 */
	public function testSlug(): void {
		$this->assertSame('hello-world', Str::slug('Hello World!'));
		$this->assertSame('', Str::slug(null));
	}

	/**
	 * Test trim method.
	 */
	public function testTrim(): void {
		$this->assertSame('Hello', Str::trim('  Hello  '));
		$this->assertSame('', Str::trim(null));
	}

	/**
	 * Test rtrim method.
	 */
	public function testRtrim(): void {
		$this->assertSame('  Hello', Str::rtrim('  Hello  '));
		$this->assertSame('', Str::rtrim(null));
	}

	/**
	 * Test ltrim method.
	 */
	public function testLtrim(): void {
		$this->assertSame('Hello  ', Str::ltrim('  Hello  '));
		$this->assertSame('', Str::ltrim(null));
	}

	/**
	 * Test reverse method.
	 */
	public function testReverse(): void {
		$this->assertSame('olleH', Str::reverse('Hello'));
		$this->assertSame('', Str::reverse(null));
	}

	/**
	 * Test repeat method.
	 */
	public function testRepeat(): void {
		$this->assertSame('HelloHello', Str::repeat('Hello', 2));
		$this->assertSame('', Str::repeat(null, 2));
	}

	/**
	 * Test replace method.
	 */
	public function testReplace(): void {
		$this->assertSame('Hi World', Str::replace('Hello', 'Hi', 'Hello World'));
		$this->assertSame('', Str::replace(null, null, null));
	}

	/**
	 * Test safe method.
	 */
	public function testSafe(): void {
		$this->assertSame('', Str::safe(null));
		$this->assertSame('&lt;div&gt;Hello&lt;/div&gt;', Str::safe('<div>Hello</div>'));
		$this->assertSame('&lt;div title=&quot;Hello&quot;&gt;World&lt;/div&gt;', Str::safe('<div title="Hello">World</div>'));
		$this->assertSame('This is &amp; test', Str::safe('This is & test'));
		$this->assertSame('', Str::safe(''));
	}
}
