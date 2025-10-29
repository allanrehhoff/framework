<?php

namespace Core;

/**
 * Basic router for handling HTTP requests and routing them to appropriate controllers
 * Follows a simple routing mechanism to match URLs and execute the corresponding controller for each route..
 */
final class Router {
	/**
	 * @var \Configuration $configuration Router related configuration
	 */
	private \Configuration $configuration;

	/**
	 * @var \Core\Request $iRequest holds the request object
	 */
	private Request $request;

	/**
	 * @var \Core\Response $iResponse holds the request object
	 */
	private Response $response;

	/**
	 * @var array{0: ClassName, 1: MethodName} $route A tuple containing [ClassName, MethodName]
	 */
	private array $route;

	/**
	 * @var Context Current route context
	 */
	private Context $context;

	/**
	 * Router constructor parses args from current environment.
	 * @param Request $iRequest The global request object
	 * @param Response $iResponse The response object
	 * @return void
	 */
	public function __construct(Request $iRequest, Response $iResponse) {
		$this->request = $iRequest;
		$this->response = $iResponse;
		$this->configuration = new \Configuration(STORAGE . "/config/router.jsonc");

		$defaults = $this->getDefaultArguments();

		$controllerBase = $this->request->getArg(0, $defaults);
		$methodNameBase = $this->request->getArg(1, $defaults);

		$iMethodName = new MethodName($methodNameBase ?? MethodName::DEFAULT);
		$iClassName = new ClassName($controllerBase);

		// Instantiating a new ReflectionClass will call autoloader
		// which will throw \Core\Exception\FileNotFound if file is not found
		// class_exists(); is not used because we'll need the reflection later
		try {
			$iReflectionClass = new \ReflectionClass($iClassName->toString());
		} catch (\Core\Exception\FileNotFound) {
			$this->route = $this->handleUnroutableRequest();
			return;
		}

		// Check if method name exists on class
		// If method is not present, fallback to default
		try {
			$iReflectionMethod = $iReflectionClass->getMethod($iMethodName->toString());
		} catch (\ReflectionException) {
			$iMethodName = $this->getDefaultMethodName();
		}

		// \Core\Exception\Governance is deliberately thrown for non-public methods
		// as end-users could accidently end up unwanted methods if simply re-routed to MethodName::DEFAULT
		// However we still want our fallback to MethodName::DEFAULT if the method is simply not defined.
		try {
			$iReflectionMethod = $iReflectionClass->getMethod($iMethodName->toString());
			if ($iReflectionMethod->isPublic() !== true) throw new \Core\Exception\Governance;
		} catch (\Core\Exception\Governance) {
			$this->route = $this->handleUnroutableRequest();
			return;
		}

		\Core\Event::trigger("core.router.found", $this->request, $iClassName, $iMethodName);

		$this->route = [$iClassName, $iMethodName];
		\Registry::set($this->route, "route");
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
	public function dispatch(ClassName $iClassName, null|MethodName $iMethodName = null, null|\Controller $parentController = null): \Controller {
		$controllerName = $iClassName->toString();
		$methodName = $iMethodName ? $iMethodName->toString() : MethodName::DEFAULT;
		$defaultMethodName = $this->getDefaultMethodName();

		$iController = new $controllerName($this->request, $this->response, $parentController);

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
			$iController = $this->dispatch($iStatusCode->getClassName(), new MethodName(MethodName::DEFAULT));
		}

		foreach ($iController->getChildren() as $childControllerName) {
			$childController = $this->dispatch($childControllerName, $defaultMethodName, $iController);
			$iController->getResponse()->setData($childController->getResponse()->getData());
		}

		return $iController;
	}

	/**
	 * Get the \Core\Request object
	 * @return Request
	 */
	public function getRequest(): Request {
		return $this->request;
	}

	/**
	 * Get the \Core\Response object
	 * @return Response
	 */
	public function getResponse(): Response {
		return $this->response;
	}

	/**
	 * Get the \Configuration object
	 * @return \Configuration
	 */
	public function getConfiguration(): \Configuration {
		return $this->configuration;
	}

	/**
	 * Returns default route from configuration
	 * @throws \Core\Exception\Governance If the default route configured is not of type array.
	 * @return array
	 */
	public function getDefaultArguments(): array {
		$defaultRoute = $this->getConfiguration()->get("defaultArgs");

		if (is_array($defaultRoute) !== true) {
			throw new \Core\Exception\Governance("Router setting 'defaultArgs' is not an array");
		}

		return $defaultRoute;
	}

	/**
	 * Get ClassName for requests that cannot be routed
	 * This is the class that will be executed when a route cannot be found
	 * @return \Core\ClassName
	 */
	public function getNotFoundClassName(): ClassName {
		return new ClassName("StatusCode\NotFound");
	}

	/**
	 * Get default MethodName to be called on controlelrs
	 * @return \Core\MethodName
	 */
	public function getDefaultMethodName(): MethodName {
		return new MethodName(MethodName::DEFAULT);
	}

	/**
	 * Handle requests that cannot be routed.
	 * @return array{0: ClassName, 1: MethodName} An array with two elements: [0] ClassName An instance of ClassName. [1] MethodName An instance of MethodName.
	 */
	public function handleUnroutableRequest(): array {
		\Core\Event::trigger("core.router.notfound", $this->request);

		$route = [$this->getNotFoundClassName(), $this->getDefaultMethodName()];

		\Registry::set($route, "route");

		return $route;
	}

	/**
	 * Returns the resolved route from path arguments
	 * First index will always be the controller to invoke
	 * second index, if present, will be the method name
	 * @return array{0: ClassName, 1: MethodName} An array with two elements: [0] ClassName An instance of ClassName. [1] MethodName An instance of MethodName.
	 */
	public function getRoute(): array {
		return $this->route;
	}
}
