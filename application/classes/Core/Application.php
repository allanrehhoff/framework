<?php
namespace Core {
	/**
	 * The main class for this application.
	 * Core\Application handles the routes, directory paths and title
	 * Consult the README file for usage examples throughout the framework.
	 * @author Allan Thue Rehhoff
	 * @uses ReflectionClass
	 * @uses Exception
	 * @uses Resource
	 * @see README.md
	 */
	final class Application {
		/**
		 * @var string Current working directory, in which the application resides.
		 */
		private $applicationPath;

		/**
		 * @var array Arguments provided through URI parts
		 */
		private $args;

		/**
		 * @var array Actual controller path routed by given args
		 */
		private $executedControllerName;

		/**
		 * @var array Method name called on the master controller
		 */
		private $calledMethodName;

		/**
		 * @var \Core\Configuration Holds the Application-wide configuration object.
		 */
		private $iConfiguration;

		/**
		 * Parse the current route and set caching as needed.
		 * @param array $args Application arguments, usually url-parts divided by /, or argv.
		 */
		public function __construct(array $args) {
			$this->applicationPath = APP;

			$configurationFile = STORAGE . "/config/application.jsonc";

			$this->iConfiguration = new Configuration($configurationFile);

			\Resource::set(new \Database\Connection(
				$this->iConfiguration->get("database.host"),
				$this->iConfiguration->get("database.username"),
				$this->iConfiguration->get("database.password"),
				$this->iConfiguration->get("database.name")
			));

			if(CLI === false) {
				$route = $args["route"] ?? $this->iConfiguration->get("default_route");
				$this->args = explode('/', ltrim($route, '/'));
			} else {
				$this->args = array_slice($args, 1);
			}
		}

		/**
		 * Get an argument from the url. ommit $argIndex to get all arguments passed with the request.
		 * This is set to a string because if the variable passed to this function 
		 * is null it would be easier to debug with a ull rather than getting the whole array.
		 * @param int $index the index or the url arg.
		 * @return null|string, or null on failure.
		 */
		public function arg(int $index = -1) : ?string {
			if(isset($this->args[$index]) && $this->args[$index] !== '') {
				return $this->args[$index];
			}

			return null;
		}

		/**
		 * Get all parsed application args
		 * @return array
		 */
		public function getArgs() : array {
			return $this->args;
		}

		/**
		 * Get controller name/path of the executed main controller
		 * @return string
		 */
		public function getExecutedControllerName() : string {
			return $this->executedControllerName;
		}

		/**
		 * Get controller name/path of the executed main controller
		 * @return string
		 */
		public function getCalledMethodName() : string {
			return $this->calledMethodName;
		}

		/**
		 * Get the current working directory of the application
		 * @return string
		 */
		public function getApplicationPath() : string {
			return $this->applicationPath;
		}

		/**
		 * Returns the configuration object associated with the application
		 * @return Configuration - application-wide configuration
		 */
		public function getConfiguration() : Configuration {
			return $this->iConfiguration;
		}

		/**
		 * Get path to the specified controller file. Ommit the .php extension
		 * @todo Cut .php from the $ctrl param, if provided. (Find out if I can use basename()'s second argument)
		 * @param string $controller name of the controller file.
		 * @return string or null on failure
		 */
		public function getControllerPath(string $controller) : ?string {
			return $this->getApplicationPath()."/controllers/".basename($controller).".php";
		}

		/**
		 * Executes a given controller by name.
		 * Reroutes to NotFouncController if a \Core\Exception\NotFound is thrown
		 * within the controller or any of it's child controllers.
		 * @throws Exception
		 * @param string $controller The controller name, alias the class name.
		 * @return Controller The dispatched controller that has just been executed.
		 */
		public function executeController(string $controllerClass, string $methodName = MethodName::DEFAULT, ?\Controller $parentController = null) : \Controller {
			$controllerClass = (string) new ControllerName($controllerClass);
			$methodName 	 = (string) new MethodName($methodName);

			if(method_exists($controllerClass, $methodName) !== true) {
				$methodName = MethodName::DEFAULT;
			}
			
			try {
				$iController = new $controllerClass;

				if($parentController !== null) {
					$iController->setParent($parentController);
					$iController->setData($parentController->getData());
				} else {
					$this->executedControllerName = $iController->getName();
					$this->calledMethodName = $methodName;
				}

				$iController->start();
				$iController->$methodName();
				$iController->stop();
			} catch(\Core\Exception\Forbidden $e) {
				$iController = $this->executeController("Forbidden");
			} catch(\Core\Exception\NotFound $e) {
				$iController = $this->executeController("NotFound");
			}

			foreach($iController->getChildren() as $childControllerName) {
				$childController = $this->executeController($childControllerName, MethodName::DEFAULT, $iController);
				$iController->setData($childController->getData());
			}

			return $iController;
		}

		/**
		 * Dispatches a controller, based upon the requeted path.
		 * Serves a NotfoundController if it doesn't exists
		 * @return Controller Instance of extended Controller
		 */
		public function run() : \Controller {
			$controllerBase = $this->arg(0);

			$arguments = [$controllerBase];

			if($this->arg(1) !== null) $arguments[] = $this->arg(1);

			return $this->executeController(...$arguments);
		}
	}
}