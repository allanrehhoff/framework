<?php
	class RouterTest extends PHPUnit\Framework\TestCase {
		private function getRouterWithArguments(array $arguments) : \Core\Router {
			$iRequest = new \Core\Request();
			$iRequest->setArguments($arguments);

			$iResponse = new \Core\Response();

			$iRouter = new \Core\Router($iRequest, $iResponse);

			return $iRouter;
		}

		private function getRouteFromArguments(array $arguments) : array {
			$iRouter = $this->getRouterWithArguments($arguments);

			$route = $iRouter->getRoute();

			return $route;
		}

		/**
		 * Test a route that exists
		 */
		public function testSimpleRoute() {
			[$controller, $method] = $this->getRouteFromArguments(["bin/app", "cli", "my-method"]);

			$this->assertInstanceOf(\Core\ClassName::class, $controller);
			$this->assertInstanceOf(\Core\MethodName::class, $method);

			$this->assertEquals(CliController::class, $controller->toString());
			$this->assertEquals("myMethod", $method->toString());
		}

		/**
		 * Test a route that routes to NotFoundController
		 */
		public function testNotFoundRoute() {
			[$controller, $method] = $this->getRouteFromArguments(["bin/app", "animal", "indo-chinese"]);

			$this->assertInstanceOf(\Core\ClassName::class, $controller);
			$this->assertInstanceOf(\Core\MethodName::class, $method);

			$this->assertEquals(NotFoundController::class, $controller->toString());
			$this->assertEquals("index", $method->toString());
		}

		/**
		 * Test that Header and Footer controllers cannot be called directly
		 */
		public function testHeaderAndFooterCannotBeCalledDirectly() {
			foreach(["Header", "Footer"] as $childController) {
				$iRouter = $this->getRouterWithArguments(["bin/app", $childController, "index"]);
				[$controller, $method] = $iRouter->getRoute();

				$this->assertInstanceOf(\Core\ClassName::class, $controller);
				$this->assertInstanceOf(\Core\MethodName::class, $method);

				$iApplication = new \Core\Application($iRouter);

				$iController = $iApplication->run();

				$this->assertInstanceOf(\NotFoundController::class, $iController);
			}
		}

		/**
		 * Test route fallback, without the method name argument given.
		 */
		public function testDefaultRoute() {
			[$controller, $method] = $this->getRouteFromArguments(["bin/app", "cli"]);

			$this->assertInstanceOf(\Core\ClassName::class, $controller);
			$this->assertInstanceOf(\Core\MethodName::class, $method);

			$this->assertEquals(CliController::class, $controller->toString());
			$this->assertEquals(\Core\MethodName::DEFAULT, $method->toString());
		}
	}