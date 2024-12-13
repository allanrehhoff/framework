<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Logger::class)]
class LoggerTest extends TestCase {
	/**
	 * Set up the test case.
	 */
	protected function setUp(): void {
		Logger::$printLog = false;
	}

	/**
	 * Tear down the test case.
	 */
	protected function tearDown(): void {
		Logger::clearLog();
	}

	/**
	 * Test the debug method.
	 */
	public function testDebug() {
		$msg = "variable x is false";

		Logger::$logLevel = 'debug';
		Logger::debug($msg);

		$result = Logger::dumpToString();

		$this->assertStringContainsString($msg, $result);
		$this->assertStringContainsStringIgnoringCase('debug', $result);
	}

	/**
	 * Test the info method.
	 */
	public function testInfo() {
		$msg = "variable x is false";

		Logger::$logLevel = 'info';
		Logger::info($msg);

		$result = Logger::dumpToString();

		$this->assertStringContainsString($msg, $result);
		$this->assertStringContainsStringIgnoringCase('info', $result);
	}

	/**
	 * Test the warning method.
	 */
	public function testWarning() {
		$msg = "variable x is false";

		Logger::$logLevel = 'warning';
		Logger::warning($msg);

		$result = Logger::dumpToString();

		$this->assertStringContainsString($msg, $result);
		$this->assertStringContainsStringIgnoringCase('warning', $result);
	}

	/**
	 * Test the error method.
	 */
	public function testError() {
		$msg = "variable x is false";

		Logger::$logLevel = 'error';
		Logger::error($msg);

		$result = Logger::dumpToString();

		$this->assertStringContainsString($msg, $result);
		$this->assertStringContainsStringIgnoringCase('error', $result);
	}

	/**
	 * Test when log level is too low.
	 */
	public function testLogLevelTooLow() {
		$msg = "variable x is false";

		Logger::$logLevel = 'info';
		Logger::debug($msg);

		$result = Logger::dumpToString();

		$this->assertSame('', $result);
	}

	/**
	 * Test a single timer.
	 */
	public function testSingleTimer() {
		$seconds = 0.5;
		$microSeconds = $seconds * 1e6;

		Logger::$logLevel = 'debug';
		Logger::time('Testing the timing');

		usleep($microSeconds);
		$result = Logger::timeEnd('Testing the timing', 6, 'debug');

		$this->assertEqualsWithDelta($seconds, $result, 0.01);
	}

	/**
	 * Test multiple timers.
	 */
	public function testMultipleTimers() {
		$seconds = 0.5;
		$microSeconds = $seconds * 1e6;

		Logger::$logLevel = 'debug';
		Logger::time('outer timer');

		usleep($microSeconds);

		Logger::time('inner timer');

		usleep($microSeconds);

		$result2 = Logger::timeEnd('inner timer', 6, 'debug');
		$result1 = Logger::timeEnd('outer timer', 6, 'debug');

		$this->assertEqualsWithDelta(2 * $seconds, $result1, 0.01);
		$this->assertEqualsWithDelta($seconds, $result2, 0.01);
	}

	/**
	 * Test timing with default parameters.
	 */
	public function testTimingWithDefaultParameters() {
		$seconds = 1;
		$microSeconds = $seconds * 1e6;

		Logger::$logLevel = 'debug';
		Logger::time();

		usleep($microSeconds);

		$result = Logger::timeEnd();

		$this->assertEqualsWithDelta($seconds, $result, 0.01);
	}
}
