<?php
	class SingletonTest extends PHPUnit\Framework\TestCase {
		public function testSimpleObjectStorage() {
			$iResponse = \ResponseFactory::new();

			\Singleton::set($iResponse);

			$this->assertInstanceOf(\Core\Response::class, \Singleton::get("Core\Response"));
		}

		public function testObjectAlias() {
			$iResponse = \ResponseFactory::new();

			\Singleton::set($iResponse, "response");

			$this->assertInstanceOf(\Core\Response::class, \Singleton::get("response"));
		}
	}