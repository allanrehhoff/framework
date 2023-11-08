<?php
	class EventEmitterTest extends \PHPUnit\Framework\TestCase {
		public function setUp() : void {
			\Core\Event::clear();
		}

		public function testCallable() {
			\Core\Event::addListener("tests.phpunit", fn(\MockEventObject $iMockEventObject) => $iMockEventObject->property = "hello world");

			$iMockEventObject = new \MockEventObject;

			\Core\Event::trigger("tests.phpunit", $iMockEventObject);

			$this->assertEquals("hello world", $iMockEventObject->property);
		}

		public function testObjectContext() {
			\Core\Event::addListener("tests.phpunit", \MockEventListener::class);

			$iMockEventObject = new \MockEventObject;

			\Core\Event::trigger("tests.phpunit", $iMockEventObject);

			$this->assertEquals("handle", $iMockEventObject->property);
		}

		public function testStaticContext() {
			\Core\Event::addListener("tests.phpunit", [\MockEventListener::class, "handleStatic"]);

			$iMockEventObject = new \MockEventObject;

			\Core\Event::trigger("tests.phpunit", $iMockEventObject);

			$this->assertEquals("handleStatic", $iMockEventObject->property);
		}

		public function testStaticContextAsString() {
			\Core\Event::addListener("tests.phpunit", "MockEventListener::handleStatic");

			$iMockEventObject = new \MockEventObject;

			\Core\Event::trigger("tests.phpunit", $iMockEventObject);

			$this->assertEquals("handleStatic", $iMockEventObject->property);
		}

		public function testObjectContextCannotBeCalledStatically() {
			\Core\Event::addListener("tests.phpunit", [\MockEventListener::class, "handle"]);

			$iMockEventObject = new \MockEventObject;

			$this->expectException(\Error::class);
			\Core\Event::trigger("tests.phpunit", $iMockEventObject);
		}
	}