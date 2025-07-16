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
	 * @var array $route The route to be executed
	 */
	private array $route;

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
	 * @return array An array with two elements: [0] ClassName An instance of ClassName. [1] MethodName An instance of MethodName.
	 */
	public function handleUnroutableRequest(): array {
		\Core\Event::trigger("core.router.notfound", $this->request);
		return [$this->getNotFoundClassName(), $this->getDefaultMethodName()];
	}

	/**
	 * Returns the resolved route from path arguments
	 * First index will always be the controller to invoke
	 * second index, if present, will be the method name
	 * @return array
	 */
	public function getRoute(): array {
		return $this->route;
	}
}
