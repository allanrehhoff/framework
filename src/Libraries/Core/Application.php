<?php
namespace Core {
	/**
	 * The main class for this application.
	 */
	final class Application {
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
			return APP_PATH."/Controllers/".basename($controller).".php";
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
		 * @param  \Core\ClassName $iClassName Name of a class representing the controller that should be executed
		 * @param ?\Core\MethodName $iMethodName Method name that should be executed on instantiated controller, default is index
		 * @param ?\Controller $parentController, execute controller with this as its parent, default null
		 * @return \Controller The executed controller that has just been executed.
		 */
		public function executeController(ClassName $iClassName, ?MethodName $iMethodName = new MethodName(MethodName::DEFAULT), ?\Controller $parentController = null) : \Controller {			
			$controllerName = $iClassName->toString();
			$methodName = $iMethodName->toString();

			$this->executedClassName = $iClassName;
			$this->calledMethodName = $iMethodName;

			try {
				$iController = new $controllerName($this);

				if($parentController !== null) {
					$iController->setParent($parentController);
					$iController->getResponse()->setData($parentController->getResponse()->getData());
				}

				$iController->start();
				$iController->$methodName();
				$iController->stop();
			} catch(\Core\HttpError\StatusCode $iStatusCode) {
				$iController->getResponse()->sendHttpCode($iStatusCode->getHttpCode());
				$iController = $this->executeController(new ClassName($iStatusCode->getClassName()));
			}

			foreach($iController->getChildren() as $childControllerName) {
				$childController = $this->executeController($childControllerName, parentController: $iController);
				$iController->getResponse()->setData($childController->getResponse()->getData());
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