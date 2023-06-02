<?php
	class ApplicationTest extends \PHPUnit\Framework\TestCase {
		private function getRouterWithArguments(array $arguments) : \Core\Router {
			$iRequest = new \Core\Request();
			$iRequest->setArguments($arguments);

			$iResponse = new \Core\Response();

			$iRouter = new \Core\Router($iRequest, $iResponse);

			return $iRouter;
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
		 * Test child controllers can set data
		 */
		public function testChildControllersCanSetData() {
			$iRouter = $this->getRouterWithArguments(["bin/app", "mock"]);
			$iApplication = new \Core\Application($iRouter);

			[$controller, $method] = $iRouter->getRoute();

			$iController = $iApplication->executeController($controller, $method);

			$data = $iController->getResponse()->getData();

			$this->assertArrayHasKey("header", $data);
			$this->assertArrayHasKey("javascript", $data);
		}
	}