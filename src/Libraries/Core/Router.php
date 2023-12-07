<?php
namespace Core {
	/**
	 * Basic router for handling HTTP requests and routing them to appropriate controllers
	 * Follows a simple routing mechanism to match URLs and execute the corresponding controller for each route..
	 */
	final class Router {
		/**
		 * @var \Core\Request $iRequest holds the request object
		 */
		private Request $request;

		/**
		 * @var \Core\Response $iResponse holds the request object
		 */
		private Response $response ;

		/**
		 * Router constructor parses args from current environment.
		 * @param Request $iRequest The global request object
		 * @param Response $iResponse The response object
		 * @return void
		 */
		public function __construct(Request $iRequest, Response $iResponse) {
			$this->request = $iRequest;
			$this->response = $iResponse;
		}

		/**
		 * Get the \Core\Request object
		 * @return Request
		 */
		public function getRequest() : Request {
			return $this->request;
		}

		/**
		 * Get the \Core\Response object
		 * @return Response
		 */
		public function getResponse() : Response {
			return $this->response;
		}

		/**
		 * Returns default route from configuration
		 * @throws \Core\Exception\Governance If the default route configured is not of type array.
		 * @return array
		 */
		public function getConfiguredDefaultRoute() : array {
			$defaultRoute = \Singleton::getConfiguration()->get("defaultRoute");

			if(is_array($defaultRoute) !== true) {
				throw new \Core\Exception\Governance("Setting 'defaultRoute' is not an array");
			}

			return $defaultRoute;
		}

		/**
		 * Get ClassName for requests that cannot be routed
		 * @return \Core\ClassName
		 */
		private function getNotFoundClassName() : ClassName {
			return new ClassName("NotFound");
		}

		/**
		 * Get default MethodName to be called on controlelrs
		 * @return \Core\MethodName
		 */
		private function getDefaultMethodName() : MethodName {
			return new MethodName(MethodName::DEFAULT);
		}

		/**
		 * Handle requests that cannot be routed.
		 * @return array An array with two elements: [0] ClassName An instance of ClassName. [1] MethodName An instance of MethodName.
		 */
		private function handleUnroutableRequest() : array {
			\Core\Event::trigger("core.router.notfound", $this->request);
			return [$this->getNotFoundClassName() , $this->getDefaultMethodName()];
		}

		/**
		 * Returns the resolved route from path arguments
		 * First index will always be the controller to invoke
		 * second index, if present, will be the method name
		 * @return array
		 */
		public function getRoute() : array {
			$defaults = $this->getConfiguredDefaultRoute();

			$controllerBase = $this->request->getArg(0, $defaults);
			$methodNameBase = $this->request->getArg(1, $defaults);

			// Instantiating a new ReflectionClass will call autoloader
			// which will throw \Core\Exception\FileNotFound if file is not found
			// class_exists(); is not used because we'll need the reflection later
			try {
				$iClassName = new ClassName($controllerBase);
				$iReflectionClass = new \ReflectionClass($iClassName->toString());
			} catch(\Core\Exception\FileNotFound) {
				return $this->handleUnroutableRequest();
			}

			// Check if method name exists on class
			// If method is not present, fallback to default
			try {
				$iMethodName = new MethodName($methodNameBase ?? MethodName::DEFAULT);
				$iReflectionMethod = $iReflectionClass->getMethod($iMethodName->toString());
			} catch(\ReflectionException) {
				$iMethodName = $this->getDefaultMethodName();
			}

			// \Core\Exception\Governance is deliberately thrown for non-public methods
			// as end-users could accidently end up unwanted methods if simply re-routed to MethodName::DEFAULT
			// However we still want our fallback to MethodName::DEFAULT if the method is simply not defined.
			try {
				$iReflectionMethod = $iReflectionClass->getMethod($iMethodName->toString());
				if($iReflectionMethod->isPublic() !== true) throw new \Core\Exception\Governance;
			} catch(\Core\Exception\Governance) {
				return $this->handleUnroutableRequest();
			}

			\Core\Event::trigger("core.router.found", $this->request, $iClassName, $iMethodName);
			
			return [$iClassName, $iMethodName];
		}
	}
}