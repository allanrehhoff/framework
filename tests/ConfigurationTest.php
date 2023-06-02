<?php
	class ConfigurationTest extends PHPUnit\Framework\TestCase {
		/**
		 * Assert that the default route has been set
		 */
		public function testGet() {
			$iConfiguration = \Resource::getConfiguration();

			$this->assertEquals("default", $iConfiguration->get("theme"));
		}

		/**
		 * Assert that the default route has been set
		 */
		public function testDefaultRoute() {
			$iConfiguration = \Resource::getConfiguration();

			$this->assertEquals(["index"], $iConfiguration->get("defaultRoute"));
		}

		/**
		 * Test dot syntax for getting config settings
		 */
		public function testDotSyntax() {
			$iConfiguration = \Resource::getConfiguration();

			$this->assertEquals("localhost", $iConfiguration->get("database.host"));
		}

		/**
		 * Test config variables/tags
		 */
		 public function testConfigVariables() {
			$iConfiguration = \Resource::getConfiguration();

			$iConfiguration->set("testkey", "{{theme}}");

			$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("theme"));
		 }

		 /**
		 * Test nested config variables/tags
		 */
		public function testNestedConfigVariables() {
			$iConfiguration = \Resource::getConfiguration();

			$iConfiguration->set("testkey", "{{database.host}}");

			$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("database.host"));
		}

		/**
		 * Test getting invalid config key fails
		 */
		public function testInvalidConfigKey() {
			$this->expectException(\InvalidArgumentException::class);
			\Resource::getConfiguration()->get("invalidkey");
		}

		/**
		 * Test delete config key
		 */
		public function testDeleteConfigKey() {
			$iConfiguration = \Resource::getConfiguration();
			$iConfiguration->set("testkey", "{{theme}}");

			$this->assertEquals($iConfiguration->get("testkey"), $iConfiguration->get("theme"));

			$iConfiguration->delete("testkey");

			$this->expectException(\InvalidArgumentException::class);
			$var = Resource::getConfiguration()->get("testkey");
		}

		/**
		 * Test deleting nested config key
		 * @todo Finnish implementing this test
		 */
		public function testDeleteNestedConfigKey() {
			$iConfiguration = \Resource::getConfiguration();
			$iConfiguration->set("test.key", "{{theme}}");
			$iConfiguration->set("test.key2", "{{theme}}");

			$this->assertEquals($iConfiguration->get("test.key"), $iConfiguration->get("theme"));

			/** @todo The delete method current throws "test.key" is not a valid configuration */
//			$this->markTestIncomplete('This test has not been implemented yet.');

			$iConfiguration->delete("test.key");

			$this->expectException(\InvalidArgumentException::class);
			\Resource::getConfiguration()->get("test.key");

			$this->assertEquals($iConfiguration->get("test.key2"), $iConfiguration->get("theme"));
		}
	}