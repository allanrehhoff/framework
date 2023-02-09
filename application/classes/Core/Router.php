<?php
namespace Core {
	class Router {
		/**
		 * @var array Arguments provided through URI parts
		 */
		private $args;

		/**
		 * @var Request $iRequest holds the request object
		 */
		private $request = null;

		/**
		 * Router constructor parses args from current environment.
		 * 
		 * @param Request $iRequest The global request object
		 * @return void
		 */
		public function __construct(Request $iRequest) {
			$this->request = $iRequest;
			$args = $iRequest->getPath();

			if(IS_CLI === false) {
				$route = trim($args, '/') != '' ? $args : \Resource::getConfiguration()->get("default_route");
				$this->args = explode('/', $route);
			} else {
				$this->args = array_slice($args, 1);
			}
		}

		/**
		 * Get the \Core\Request object
		 */
		public function getRequest() : Request {
			return $this->request;
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

			$methodName = $this->arg(1) !== null ? $this->arg(1) : MethodName::DEFAULT;

			$iControllerName = new ControllerName($controllerBase);
			$iMethodName 	 = new MethodName($methodName);

			if(class_exists($iControllerName->toString()) === false) {
				$iControllerName = new ControllerName("NotFound");
			}

			if(method_exists($iControllerName, $iMethodName) !== true) {
				$iMethodName = new MethodName(MethodName::DEFAULT);
			}

			return [$iControllerName, $iMethodName];
		}
	}
}