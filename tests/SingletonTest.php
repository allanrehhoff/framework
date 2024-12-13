<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Singleton::class)]
class SingletonTest extends TestCase {
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