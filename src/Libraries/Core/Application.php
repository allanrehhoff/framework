<?php

namespace Core;

/**
 * The main class for this application.
 */
final class Application {

	/**
	 * @var ClassName Actual controller path routed by given args
	 */
	private ClassName $executedClassName;

	/**
	 * @var MethodName Method name called on the master controller
	 */
	private MethodName $calledMethodName;

	/**
	 * @var Router The router responsible for parsing request uri to callable system path
	 */
	private Router $router;

	/**
	 * Parse the current route and set caching as needed.
	 * 
	 * @param \Core\Router $iRouter Application arguments, usually url-parts divided by /, or argv.
	 */
	public function __construct(Router $iRouter) {
		$this->router = $iRouter;
	}

	/**
	 * Get controller name/path of the executed main controller
	 * 
	 * @return ClassName Called controller name
	 */
	public function getExecutedClassName(): ClassName {
		return $this->executedClassName;
	}

	/**
	 * Get controller name/path of the executed main controller
	 * 
	 * @return MethodName Called method name
	 */
	public function getCalledMethodName(): MethodName {
		return $this->calledMethodName;
	}

	/**
	 * Get path to the specified controller file. Omit the .php extension
	 * 
	 * @param string $controller Name of the controller file.
	 * @return ?string|null On failure
	 */
	public function getControllerPath(string $controller): ?string {
		return APP_PATH . "/Controllers/" . basename($controller) . ".php";
	}

	/**
	 * Get the router being used
	 * 
	 * @return \Core\Router
	 */
	public function getRouter(): Router {
		return $this->router;
	}

	/**
	 * Executes a given controller by name.
	 * Reroutes to NotFoundController if a \Core\Exception\NotFound is thrown
	 * within the controller or any of its child controllers.
	 * 
	 * @param \Core\ClassName         $iClassName      Name of a class representing the controller that should be executed
	 * @param null|\Core\MethodName   $iMethodName     Method name that should be executed on instantiated controller, default is index
	 * @param null|\Controller        $parentController Execute controller with this as its parent, default null
	 * 
	 * @return \Controller The executed controller that has just been executed.
	 */
	public function executeController(ClassName $iClassName, ?MethodName $iMethodName = null, ?\Controller $parentController = null): \Controller {
		$controllerName = $iClassName->toString();
		$methodName = $iMethodName ? $iMethodName->toString() : MethodName::DEFAULT;
		$defaultMethodName = $this->getRouter()->getDefaultMethodName();

		// Only record the top-level controller
		if ($parentController === null) {
			$this->executedClassName = $iClassName;
			$this->calledMethodName = $iMethodName ?? $defaultMethodName;
		}

		$iController = new $controllerName($this, $parentController);

		// Pass response data from parent
		// controller to child controller
		if ($parentController !== null) {
			$iController->getResponse()->setData($parentController->getResponse()->getData());
		}

		try {
			\Core\Event::trigger("core.controller.method.before", $iController, $iMethodName);

			$iController->$methodName();

			\Core\Event::trigger("core.controller.method.after", $iController, $iMethodName);
		} catch (\Core\StatusCode\StatusCode $iStatusCode) {
			$iController = $this->executeController($iStatusCode->getClassName(), new MethodName(MethodName::DEFAULT));
		}

		foreach ($iController->getChildren() as $childControllerName) {
			$childController = $this->executeController($childControllerName, $defaultMethodName, $iController);
			$iController->getResponse()->setData($childController->getResponse()->getData());
		}

		return $iController;
	}

	/**
	 * Dispatches a controller, based upon the requested path.
	 * Serves a NotFoundController if it doesn't exist
	 * 
	 * @return \Controller Instance of extended Controller
	 */
	public function run(): \Controller {
		return $this->executeController(...$this->router->getRoute());
	}
}
