<?php
namespace Core {
	/**
	 * The main class for this application.
	 */
	final class Application {
		/**
		 * @var string Current working directory, in which the application resides.
		 */
		private $applicationPath;

		/**
		 * @var ClassName Actual controller path routed by given args
		 */
		private $executedClassName;

		/**
		 * @var MethodName Method name called on the master controller
		 */
		private $calledMethodName;

		/**
		 * @var Router The router responsible for parsing request uri to callable system path
		 */
		private $router;

		/**
		 * Parse the current route and set caching as needed.
		 * 
		 * @param array $args Application arguments, usually url-parts divided by /, or argv.
		 */
		public function __construct(Router $iRouter) {
			$this->router = $iRouter;
		}

		/**
		 * Get controller name/path of the executed main controller
		 * 
		 * @return ClassName Called controller name
		 */
		public function getExecutedClassName() : ClassName {
			return $this->executedClassName;
		}

		/**
		 * Get controller name/path of the executed main controller
		 * 
		 * @return MethodName Called method name
		 */
		public function getCalledMethodName() : MethodName {
			return $this->calledMethodName;
		}

		/**
		 * Get path to the specified controller file. Ommit the .php extension
		 * 
		 * @param string $controller name of the controller file.
		 * @return string or null on failure
		 */
		public function getControllerPath(string $controller) : ?string {
			return APP_PATH."/controllers/".basename($controller).".php";
		}

		/**
		 * Get the router being used
		 */
		public function getRouter() : Router {
			return $this->router;
		}

		/**
		 * Executes a given controller by name.
		 * Reroutes to NotFouncController if a \Core\Exception\NotFound is thrown
		 * within the controller or any of it's child controllers.
		 * 
		 * @param string $controller The controller name, alias the class name.
		 * @return \Controller The dispatched controller that has just been executed.
		 */
		public function executeController(ClassName $iClassName, ?MethodName $iMethodName = MethodName::DEFAULT, ?\Controller $parentController = null) : \Controller {			
			$controllerName = $iClassName->toString();
			$methodName = $iMethodName->toString();

			$this->executedClassName = $iClassName;
			$this->calledMethodName = $iMethodName;

			try {
				$iController = new $controllerName($this);

				if($parentController !== null) {
					$iController->setParent($parentController);
					$iController->setData($parentController->getData());
				}
				
				$iController->start();
				$iController->$methodName();
				$iController->stop();
			} catch(\Core\Exception\Forbidden $e) {
				$iController = $this->executeController(new ClassName("Forbidden"));
			} catch(\Core\Exception\NotFound $e) {
				$iController = $this->executeController(new ClassName("NotFound"));
			}

			foreach($iController->getChildren() as $childControllerName) {
				$childController = $this->executeController($childControllerName, new MethodName(MethodName::DEFAULT), $iController);
				$iController->setData($childController->getData());
			}

			return $iController;
		}

		/**
		 * Dispatches a controller, based upon the requeted path.
		 * Serves a NotfoundController if it doesn't exists
		 * 
		 * @return \Controller Instance of extended Controller
		 */
		public function run() : \Controller {
			return $this->executeController(...$this->router->getRoute());
		}
	}
}