<?php
namespace Core {
	/**
	 * Basic router for handling HTTP requests and routing them to appropriate controllers
	 * Follows a simple routing mechanism to match URLs and execute the corresponding controller for each route..
	 */
	class Router {
		/**
		 * @var array Arguments provided through URI parts
		 */
		private $args;

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
		 * 
		 * @param Request $iRequest The global request object
		 * @param Response $iResponse The response object
		 * @return void
		 */
		public function __construct(Request $iRequest, Response $iResponse) {
			$this->request = $iRequest;
			$this->response = $iResponse;
			$args = $iRequest->getArguments();

			if(IS_CLI === false) {
				$defaultRoute = \Singleton::getConfiguration()->get("defaultRoute");

				if(is_array($defaultRoute) !== true) {
					throw new \Core\Exception\Governance("Setting 'defaultRoute' is not an array");
				}

				$this->args = empty($args) ? $defaultRoute : $args;
			} else {
				$this->args = array_slice($args, 1);
			}
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
		 * Get an argument from the url. ommit $argIndex to get all arguments passed with the request.
		 * This is set to a string because if the variable passed to this function 
		 * is null it would be easier to debug with a ull rather than getting the whole array.
		 * 
		 * @param int $index the index or the url arg.
		 * @return ?string, or null on failure.
		 */
		public function arg(int $index = -1) : ?string {
			if(isset($this->args[$index]) && $this->args[$index] !== '') {
				return $this->args[$index];
			}

			return null;
		}

		/**
		 * Get all parsed application args
		 * 
		 * @return array
		 */
		public function getArgs() : array {
			return $this->args;
		}

		/**
		 * Returns the resolved route from path arguments
		 * First index will always be the controller to invoke
		 * second index, if present, will be the method name
		 * 
		 * @return array
		 */
		public function getRoute() : array {
			$controllerBase = $this->arg(0);
			$methodNameBase = $this->arg(1);

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