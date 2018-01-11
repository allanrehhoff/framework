<?php
namespace Core {
	use Exception, Registry, ReflectionClass;
	
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
		private $cwd;

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
			$this->cwd = CWD;
			$this->configuration = Registry::set(new Configuration($this->cwd."/config.json"));

			if(CLI === false) {
				$route = ((isset($args["route"])) && ($args["route"] != '')) ? $args["route"] : $this->configuration->get("default_route");
				$this->args = explode('/', ltrim($route, '/'));
			} else {
				$this->args = array_slice($args, 1);
			}
		}

		/**
		* Get an argument from the url. ommit $argIndex to get all arguments passed with the request.
		* This is set to a string because if the variable passed to this function 
		* is null it would be easier to debug with a false rather than getting the whole array.
		* @param (int) $index the index or the url arg.
		* @return string, or false on failure.
		*/
		public function arg($index = "all") {
			if($index === "all") {
				return $this->args;
			} elseif(isset($this->args[$index])) {
				return $this->args[$index];
			}

			return false;
		}

		/**
		* Get the current working directory of the application
		* @return string
		*/
		public function getApplicationPath() {
			return $this->cwd;
		}

		/**
		* Get path to the specified controller file. Ommit the .php extension
		* @todo Cut .php from the $ctrl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) $controller name of the controller file.
		* @return mixed
		*/
		public function getControllerPath($controller = null) {
			if($controller === null) {
				$controller = $this->arg(0);
			}

			if(is_file($this->getApplicationPath()."/application/controllers/".basename($controller).".php")) {
				return $this->getApplicationPath()."/application/controllers/".basename($controller).".php";
			} else {
				return false;
			}
		}

		/**
		* Dispatches a controller, based upon the requeted path..
		* Serves a NotfoundController if it doesn't exists
		* @return array
		*/
		public function dispatch() {
			$base = ucwords(preg_replace("/\W+/", ' ', strtolower($this->arg(0))));

			if($this->getControllerPath($base) === false) {
				$base = "Notfound";
			}

			$controller = str_replace(" ", '', $base."Controller");
			$reflector  = new ReflectionClass($controller);

			if($reflector->isSubclassOf("Core\Controller") !== true) {
				throw new Exception($controller." must derive from \Core\Controller 'extends \Core\Controller'.");
			}

			$this->controller = new $controller;

			$method = "index";
			if($this->arg(1) !== false && $reflector->hasMethod($this->arg(1))) {
				$method = lcfirst(preg_replace("/\W+/", ' ', strtolower($this->arg(1))));
			}

			$this->controller->$method();
			$this->controller->assemble();

			return $this->controller;
		}
	}
}