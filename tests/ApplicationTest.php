<?php
	class ApplicationTest extends \PHPUnit\Framework\TestCase {
		public function testExecuteController() {
			$iRouter = \RouterFactory::withRequestArguments(["mock"]);
			$iApplication = new \Core\Application($iRouter);

			[$controller, $method] = $iRouter->getRoute();

			$iApplication->executeController($controller, $method);

			$this->assertEquals(MockController::class, $iApplication->getExecutedClassName()->toString());
			$this->assertEquals(\Core\MethodName::DEFAULT, $iApplication->getCalledMethodName()->toString());
		}

		/**
		 * Test that Header and Footer controllers cannot be called directly
		 */
		public function testHeaderAndFooterCannotBeCalledDirectly() {
			foreach(["Header", "Footer"] as $childController) {
				$iRouter = \RouterFactory::withRequestArguments([$childController, "index"]);
				[$controller, $method] = $iRouter->getRoute();

				$this->assertInstanceOf(\Core\ClassName::class, $controller);
				$this->assertInstanceOf(\Core\MethodName::class, $method);

				$iApplication = new \Core\Application($iRouter);

				$iController = $iApplication->run();

				$this->assertInstanceOf(\NotFoundController::class, $iController);
			}
		}

		/**
		 * Test private methods does not route
		 */
		public function testPrivateMethodsDoesNotRoute() {
			$iRouter = \RouterFactory::withRequestArguments(["mock", "private-function"]);
			$iApplication = new \Core\Application($iRouter);

			[$controller, $method] = $iRouter->getRoute();

			$iController = $iApplication->executeController($controller, $method);

			$this->assertInstanceOf(NotFoundController::class, $iController);
		}

		/**
		 * Test private methods does not route
		 */
		public function testProtectedMethodsDoesNotRoute() {
			$iRouter = \RouterFactory::withRequestArguments(["mock", "protected-function"]);
			$iApplication = new \Core\Application($iRouter);

			[$controller, $method] = $iRouter->getRoute();

			$iController = $iApplication->executeController($controller, $method);

			$this->assertInstanceOf(NotFoundController::class, $iController);
		}
	}