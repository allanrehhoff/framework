<?php
	class RequestTest extends PHPUnit\Framework\TestCase {
		public function testGetArg() {
			$iRequest = \RequestFactory::withArguments(["cli"]);

			$this->assertEquals($iRequest->getArg(0), "cli");
		}

		public function testGetArgWithDefaults() {
			$iRequest = \RequestFactory::withArguments(["cli"]);

			$this->assertEquals($iRequest->getArg(1, ["cli", "default"]), "default");
		}

		public function testGetArgOutOfRange() {
			$iRequest = \RequestFactory::withArguments(["cli"]);

			$this->assertNull($iRequest->getArg(2, ["cli", "default"]));
		}
	}