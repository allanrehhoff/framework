<?php
	/**
	 * Tests the \Environemnt class is able to interact
	 * with local (OS & putenv) and global (thirdparty)
	 * environment variables.
	 */
	class EnvironmentTest extends PHPUnit\Framework\TestCase {
		private $_env;

		public function setUp() : void {
			$this->_env = $_ENV;
		}

		public function tearDown() : void {
			$_ENV = $this->_env;
		}

		public function testSimpleEnvironmentVariable() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				PHP_ENV=development
			");

			$this->assertEquals("development", $iEnvironment->get("PHP_ENV"));
		}

		public function testEnvironmentVariableInSection() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[PHP]
				ENV=development
			");

			$this->assertEquals("development", $iEnvironment->get("PHP.ENV"));
		}

		public function testMultipleVariablesInSection() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[RECIPE1]
				TOPPING=cheese
				BASE=flour
			");

			$this->assertEquals("cheese", $iEnvironment->get("RECIPE1.TOPPING"));
			$this->assertEquals("flour", $iEnvironment->get("RECIPE1.BASE"));
		}

		public function testGetValuesBySectionName() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[RECIPE2]
				TOPPING=cheese
				BASE=flour
			");

			$this->assertEquals(["TOPPING" => "cheese", "BASE" => "flour"], $iEnvironment->get("RECIPE2"));
		}

		public function testDotNotationSection() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[ANIMALS.SPECIES]
				NAME=Asian Rhino
				EXTINCT=true
			");

			$this->assertEquals(["NAME" => "Asian Rhino", "EXTINCT" => "1"], $iEnvironment->get("ANIMALS.SPECIES"));
		}

		public function testSectionArray() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[HUMANKIND]
				GENDERS[]=male
				GENDERS[]=female
			");

			$this->assertEquals(["male", "female"], $iEnvironment->get("HUMANKIND.GENDERS"));
		}

		public function testSemicolonCommentsAreTreatedAsInvalid() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[RECIPE3]
				;TOPPING=cheese
			");

			$this->expectException(\InvalidArgumentException::class);
			$iEnvironment->get("RECIPE3.TOPPING");
		}

		public function testHashtagCommentsAreTreatedAsInvalid() {
			$iEnvironment = \EnvironmentFactory::createFromString("
				[RECIPE4]
				#BASE=flour
			");

			$this->expectException(\InvalidArgumentException::class);
			$iEnvironment->get("RECIPE3.BASE");
		}

		public function testCasingIsConvertedToUppercase() {
			\EnvironmentFactory::createFromString("
				[animal]
				race=tiger
			");

			$this->assertArrayHasKey("ANIMAL", $_ENV);
			$this->assertArrayHasKey("RACE", $_ENV["ANIMAL"]);
		}

		public function testPutSimpleVariableToSuperGlobal() {
			$iEnvironment = \EnvironmentFactory::fromCleanState();
			$iEnvironment->put("TESTVAR", "hello world");

			$this->arrayHasKey("TESTVAR", $_ENV);
			$this->assertEquals("hello world", $_ENV["TESTVAR"]);
		}

		public function testPutSimpleVariableToLocal() {
			$iEnvironment = \EnvironmentFactory::fromCleanState();
			$iEnvironment->put("TESTVAR2", "hello country");

			$this->assertEquals("hello country", $iEnvironment->get("TESTVAR2"));
		}

		public function testPutArrayToSection() {
			$iEnvironment = \EnvironmentFactory::fromCleanState();
			$iEnvironment->put("SECTION", ["ONE" => "hello world"]);

			$this->assertEquals("hello world", $iEnvironment->get("SECTION.ONE"));
		}

		public function testPutDotNotation() {
			$iEnvironment = \EnvironmentFactory::fromCleanState();
			$iEnvironment->put("SECTION.TWO", "star wars");

			$this->assertEquals("star wars", $iEnvironment->get("SECTION.TWO"));
		}
	}