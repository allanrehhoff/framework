<?php
namespace Core {
	use Exception;
	use Registry;
	use ReflectionClass;
	
	/**
	* The main class for this application.
	* Core\Application handles the routes, directory paths and title
	* Consult the README file for usage examples throughout the framework.
	* @author Allan Thue Rehhoff
	* @uses ReflectionClass
	* @uses Exception
	* @uses Registry
	* @see README.md
	*/
	final class Application {
		/**
		* @var Current working directory, in which the application resides.
		*/
		private $app;

		/**
		* @var arguments provided through URI parts
		*/
		private $args;

		/**
		* @var Holds the Application-wide configuration object.
		*/
		private $configuration;

		/**
		* @var Controller to be dispatched.
		*/
		private $controller;

		/**
		* Parse the current route and set caching as needed.
		*/
		public function __construct(array $args) {
			$this->app = APP;

			$configurationFile = STORAGE . "/config/application.json";

			$this->configuration = new Configuration($configurationFile);

			// \Registry::set(new \Database\Connection(
			// 	$this->configuration->get("database.host"),
			// 	$this->configuration->get("database.username"),
			// 	$this->configuration->get("database.password"),
			// 	$this->configuration->get("database.name")
			// ));

			if(CLI === false) {
				$route = $args["route"] ?? $this->configuration->get("default_route");
				$this->args = explode('/', ltrim($route, '/'));
			} else {
				$this->args = array_slice($args, 1);
			}
		}

		/**
		* Get an argument from the url. ommit $argIndex to get all arguments passed with the request.
		* This is set to a string because if the variable passed to this function 
		* is null it would be easier to debug with a ull rather than getting the whole array.
		* @param (int) $index the index or the url arg.
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
		* Get the current working directory of the application
		* @return string
		*/
		public function getApplicationPath() : string {
			return $this->app;
		}

		/**
		* Get path to the specified controller file. Ommit the .php extension
		* @todo Cut .php from the $ctrl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) $controller name of the controller file.
		* @return string or null on failure
		*/
		public function getControllerPath(string $controller = null) : ?string {
			if($controller === null) {
				$controller = $this->arg(0);
			}

			if(is_file($this->getApplicationPath()."/application/controllers/".basename($controller).".php")) {
				return $this->getApplicationPath()."/application/controllers/".basename($controller).".php";
			} else {
				return null;
			}
		}

		/**
		* Executes a given controller by name.
		* @param (string) $base The base name of the controller, alias the class name.
		* @return (object) Controller - the dispatched controller that has just been executed.
		*/
		public function executeController(string $base) : \Controller {
			$iControllerName = new ControllerName($base);
			$controller = $iControllerName->getSanitiedControllerName();

			$iReflector = new ReflectionClass($controller);

			if($iReflector->isSubclassOf("Controller") !== true) {
				throw new Exception($controller." must derive from \Controller 'extends \Controller'.");
			}

			$iController = new $controller;

			$method = '';
			if($this->arg(1) !== null) {
				$method = (string) new MethodName($this->arg(1));
			}

			if($iReflector->hasMethod($method) !== true) {
				$method = MethodName::DEFAULT;
			}

			$iController->initialize();
			$iController->$method();
			$iController->finalize();

			return $iController;
		}

		/**
		 * Returns the configuration object associated with the application
		 * @return Configuration - application-wide configuration
		 */
		public function getConfiguration() : Configuration {
			return $this->configuration;
		}

		/**
		* Dispatches a controller, based upon the requeted path.
		* Serves a NotfoundController if it doesn't exists
		* @return Instance of extended Controller
		*/
		public function run() : \Controller {
			return $this->executeController($this->arg(0));
		}
	}
}