<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase {
	public function testViewFromToplevelWithoutChildren() {
		$iRouter = \RouterFactory::withRequestArguments(["mock", "without-children"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$iController = $iApplication->executeController($controller, $method);

		$this->assertEquals("without-children", $iController->getResponse()->getView());
	}

	public function testViewFromChildren() {
		$iRouter = \RouterFactory::withRequestArguments(["mock"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$iController = $iApplication->executeController($controller, $method);

		$this->assertEquals("mockchild", $iController->getResponse()->getView());
	}

	/**
	 * Test child controllers can set data
	 */
	public function testChildControllersCanSetData() {
		$iRouter = \RouterFactory::withRequestArguments(["mock"]);
		$iApplication = new \Core\Application($iRouter);

		[$controller, $method] = $iRouter->getRoute();

		$iController = $iApplication->executeController($controller, $method);

		$data = $iController->getResponse()->getData();

		$this->assertArrayHasKey(MockChildController::$testkey, $data);
	}
}