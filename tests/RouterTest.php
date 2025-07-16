<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Router::class)]
class RouterTest extends TestCase {
	private function getRouteFromArguments(array $arguments) : array {
		$iRouter = \RouterFactory::withRequestArguments($arguments);

		return $iRouter->getRoute();
	}

	/**
	 * Test a route that exists
	 */
	public function testSimpleRoute() {
		[$controller, $method] = $this->getRouteFromArguments(["cli", "my-method"]);

		$this->assertInstanceOf(\Core\ClassName::class, $controller);
		$this->assertInstanceOf(\Core\MethodName::class, $method);

		$this->assertEquals(\CliController::class, $controller->toString());
		$this->assertEquals("myMethod", $method->toString());
	}

	/**
	 * Test a route that routes to NotFoundController
	 */
	public function testNotFoundRoute() {
		[$controller, $method] = $this->getRouteFromArguments(["animal", "indo-chinese"]);

		$this->assertInstanceOf(\Core\ClassName::class, $controller);
		$this->assertInstanceOf(\Core\MethodName::class, $method);

		$this->assertEquals(\StatusCode\NotFoundController::class, $controller->toString());
		$this->assertEquals("index", $method->toString());
	}

	/**
	 * Test route fallback, without the method name argument given.
	 */
	public function testDefaultRoute() {
		[$controller, $method] = $this->getRouteFromArguments(["cli"]);

		$this->assertInstanceOf(\Core\ClassName::class, $controller);
		$this->assertInstanceOf(\Core\MethodName::class, $method);

		$this->assertEquals(CliController::class, $controller->toString());
		$this->assertEquals(\Core\MethodName::DEFAULT, $method->toString());
	}

	/**
	 * Trying to access a private or protected controller method
	 * must result in a not found page being served.
	 */
	public function testNonPublicMethodDoesNotRoute() {
		[$controller, $method] = $this->getRouteFromArguments(["mock-controller", "private-method"]);

		$this->assertInstanceOf(\Core\ClassName::class, $controller);
		$this->assertInstanceOf(\Core\MethodName::class, $method);

		$this->assertEquals(\StatusCode\NotFoundController::class, $controller->toString());
		$this->assertEquals("index", $method->toString());
	}
}