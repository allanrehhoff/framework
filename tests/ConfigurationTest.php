<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase {
	/**
	 * Assert that the default route has been set
	 */
	public function testGet() {
		$iConfiguration = \Registry::getConfiguration();

		$this->assertEquals("default", $iConfiguration->get("theme"));
	}

	/**
	 * Test dot syntax for getting config settings
	 */
	public function testDotSyntax() {
		$iConfiguration = \Registry::getConfiguration();

		$this->assertEquals("localhost", $iConfiguration->get("database.host"));
	}

	/**
	 * Test config variables/tags
	 */
	public function testConfigVariables() {
		$iConfiguration = \Registry::getConfiguration();

		$iConfiguration->set("testkey", "{{theme}}");

		$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("theme"));
	}

		/**
	 * Test nested config variables/tags
	 */
	public function testNestedConfigVariables() {
		$iConfiguration = \Registry::getConfiguration();

		$iConfiguration->set("testkey", "{{database.host}}");

		$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("database.host"));
	}

	/**
	 * Test getting invalid config key fails
	 */
	public function testInvalidConfigKey() {
		$this->expectException(\InvalidArgumentException::class);
		\Registry::getConfiguration()->get("invalidkey");
	}

	/**
	 * Test delete config key
	 */
	public function testDeleteConfigKey() {
		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("testkey", "{{theme}}");

		$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("theme"));

		$iConfiguration->delete("testkey");

		$this->expectException(\InvalidArgumentException::class);
		$var = \Registry::getConfiguration()->get("testkey");
	}

	/**
	 * Test deleting nested config key
	 */
	public function testDeleteNestedConfigKey() {
		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("test.key", "{{theme}}");
		$iConfiguration->set("test.key2", "{{theme}}");

		$this->assertEquals($iConfiguration->get("test.key"), $iConfiguration->get("theme"));

		$iConfiguration->delete("test.key");

		$this->expectException(\InvalidArgumentException::class);
		\Registry::getConfiguration()->get("test.key");

		$this->assertEquals($iConfiguration->get("test.key2"), $iConfiguration->get("theme"));
	}

	/**
	 * Test that environment variables can be pulled from config
	 */
	public function testEnvironmentFunction() {
		putenv("FRAMEWORK_TEST=TEST1");

		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("envkey", "{{getenv('FRAMEWORK_TEST')}}");

		$this->assertEquals("TEST1", $iConfiguration->get("envkey"));
	}
	
	/**
	 * Test that constants can be pulled from config
	 */
	public function testConstantFunction() {
		$expect = PHP_VERSION;

		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("constkey", "{{constant('PHP_VERSION')}}");

		$this->assertEquals($expect, $iConfiguration->get("constkey"));
	}

	/**
	 * Test that constants can be pulled from config
	 */
	public function testInigetFunction() {
		$expect = ini_get("display_errors");

		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("inikey", "{{ini_get('display_errors')}}");

		$this->assertEquals($expect, $iConfiguration->get("inikey"));
	}

	/**
	 * Test that not-allowed functions cannot be used
	 */
	public function testDisallowedFunction() {
		$expect = ini_get("display_errors");

		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("setkey", "{{ini_set('display_errors', 1)}}");

		$this->expectException(\InvalidArgumentException::class);
		$iConfiguration->get("setkey");
	}

	/**
	 * Test that using a function does not collide with
	 * a manually set setting
	 */
	public function testFunctionsDoesNotCollide() {
		$expect = PHP_VERSION;

		$iConfiguration = \Registry::getConfiguration();
		$iConfiguration->set("constant", "myownvalue");
		$iConfiguration->set("constkey", "{{constant('PHP_VERSION')}}");

		$this->assertNotEquals($expect, $iConfiguration->get("constkey"));
		$this->assertEquals("myownvalue", $iConfiguration->get("constkey"));
	}

	/**
	 * Test that passing a non-existant file throws exception
	 */
	public function testFileNotFoundException() {
		$this->expectException(\Core\Exception\FileNotFound::class);

		new \Configuration("filenotfound.jsonc");
	}
}