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
		private $request = null;

		/**
		 * @var \Core\Response $iResponse holds the request object
		 */
		private $response = null;

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
		 * @throws \Core\Exception\Governance
		 * @return array
		 */
		public function getDefaultRoute() : array {
			$defaultRoute = \Singleton::getConfiguration()->get("defaultRoute");

			if(is_array($defaultRoute) !== true) {
				throw new \Core\Exception\Governance("Setting 'defaultRoute' is not an array");
			}

			return $defaultRoute;
		}

		/**
		 * Returns the resolved route from path arguments
		 * First index will always be the controller to invoke
		 * second index, if present, will be the method name
		 * @return array
		 */
		public function getRoute() : array {
			$defaults = $this->getDefaultRoute();

			$controllerBase = $this->request->getArg(0, $defaults);
			$methodNameBase = $this->request->getArg(1, $defaults);

			// Instantiating a new ReflectionClass will call autoloader
			// which will throw \Core\Exception\FileNotFound if file is not found
			// class_exists(); is not used because we'll need the reflection later
			try {
				$iClassName = new ClassName($controllerBase);
				$iReflectionClass = new \ReflectionClass($iClassName->toString());
			} catch(\Core\Exception\FileNotFound) {
				return [new ClassName("NotFound"), new MethodName(MethodName::DEFAULT)];
			}

			// \Core\Exception\Governance is deliberately thrown for non-public methods
			// as end-users could accidently end up unwanted methods if simply re-routed to MethodName::DEFAULT
			// However we still want our fallback to MethodName::DEFAULT if the method is simply not defined.
			try {
				$iMethodName = new MethodName($methodNameBase ?? MethodName::DEFAULT);
				$iReflectionMethod = $iReflectionClass->getMethod($iMethodName->toString());
				if($iReflectionMethod->isPublic() !== true) throw new \Core\Exception\Governance;
			} catch(\ReflectionException) {
				$iMethodName = new \Core\MethodName(MethodName::DEFAULT);
			} catch(\Core\Exception\Governance) {
				$iClassName = new ClassName("NotFound");
				$iMethodName = new MethodName(MethodName::DEFAULT);
			}

			return [$iClassName, $iMethodName];
		}
	}
}