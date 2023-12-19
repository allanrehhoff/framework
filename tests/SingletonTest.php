<?php
	class SingletonTest extends PHPUnit\Framework\TestCase {
		public function testSimpleObjectStorage() {
			$iResponse = \ResponseFactory::new();

			\Registry::set($iResponse);

			$this->assertInstanceOf(\Core\Response::class, \Registry::get("Core\Response"));
		}

		public function testObjectAlias() {
			$iResponse = \ResponseFactory::new();

			\Registry::set($iResponse, "response");

			$this->assertInstanceOf(\Core\Response::class, \Registry::get("response"));
		}
	}