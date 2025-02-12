<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Unit tests for the UUID class.
 */

#[CoversClass(UUID::class)]
class UUIDTest extends TestCase {

	/**
	 * Test UUID v4 generation.
	 */
	public function testV4UUID() {
		$uuid = UUID::v4();

		// Ensure the UUID has the correct format (8-4-4-4-12)
		$this->assertMatchesRegularExpression(
			'/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/',
			$uuid,
			"UUID v4 does not match the expected format."
		);
	}

/**
 * Test UUID v7 generation.
 */
public function testV7UUID() {
	$uuid = UUID::v7();

	// Ensure the UUID has the correct format (8-4-4-4-12)
	$this->assertMatchesRegularExpression(
		'/^[a-f0-9]{8}-[a-f0-9]{4}-7[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/',
		$uuid,
		"UUID v7 does not match the expected format."
	);
}
	/**
	 * Test that multiple UUID v4 calls generate unique values.
	 */
	public function testV4UUIDUniqueness() {
		$uuid1 = UUID::v4();
		$uuid2 = UUID::v4();
		$this->assertNotEquals($uuid1, $uuid2, "UUID v4 generation should produce unique values.");
	}

	/**
	 * Test that multiple UUID v7 calls generate unique values.
	 */
	public function testV7UUIDUniqueness() {
		$uuid1 = UUID::v7();
		$uuid2 = UUID::v7();
		$this->assertNotEquals($uuid1, $uuid2, "UUID v7 generation should produce unique values.");
	}

	/**
	 * Test that UUID v7 is time-ordered.
	 */
	public function testV7TimeOrdering() {
		$uuid1 = UUID::v7();
		usleep(1000); // Ensure a small time gap
		$uuid2 = UUID::v7();

		$timestamp1 = hexdec(substr($uuid1, 0, 8) . substr($uuid1, 9, 4));
		$timestamp2 = hexdec(substr($uuid2, 0, 8) . substr($uuid2, 9, 4));

		$this->assertLessThan($timestamp2, $timestamp1, "UUID v7 should be time-ordered.");
	}
}
