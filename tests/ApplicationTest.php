<?php

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Application::class)]
class ApplicationTest extends \PHPUnit\Framework\TestCase {
	public function testRouteToControllerAndMethod() {
		$iRouter = \RouterFactory::withRequestArguments(["mock"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$this->assertEquals(MockController::class, $controller->toString());
		$this->assertEquals(\Core\MethodName::DEFAULT, $method->toString());
	}

	/**
	 * Test that Header and Footer controllers cannot be called directly
	 */
	public function testHeaderAndFooterCannotBeCalledDirectly() {
		foreach (["Partial\Header", "Partial\Footer"] as $childController) {
			$iRouter = \RouterFactory::withRequestArguments([$childController, "index"]);
			$iApplication = new \Core\Application($iRouter);
			[$controller, $method] = $iRouter->getRoute();

			$this->assertInstanceOf(\Core\ClassName::class, $controller);
			$this->assertInstanceOf(\Core\MethodName::class, $method);

			$iController = $iRouter->dispatch($controller, $method);

			$this->assertInstanceOf(\StatusCode\NotFoundController::class, $iController);
		}
	}

	/**
	 * Test private methods does not route
	 */
	public function testPrivateMethodsDoesNotRoute() {
		$iRouter = \RouterFactory::withRequestArguments(["mock", "private-function"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$iController = $iRouter->dispatch($controller, $method);

		$this->assertInstanceOf(\StatusCode\NotFoundController::class, $iController);
	}

	/**
	 * Test private methods does not route
	 */
	public function testProtectedMethodsDoesNotRoute() {
		$iRouter = \RouterFactory::withRequestArguments(["mock", "protected-function"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$iController = $iRouter->dispatch($controller, $method);

		$this->assertInstanceOf(\StatusCode\NotFoundController::class, $iController);
	}
}
