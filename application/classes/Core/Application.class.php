<?php
namespace Core {
	use Exception;
	
	/**
	* The main class for this application.
	* Core\Application handles the routes, directory paths and title
	* Consult the README file for usage examples throughout the framework.
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	* @see README.md
	*/
	class Application {
		private $cwd, $args, $config, $view, $controller;

		public function __construct() {
			$this->cwd = getcwd();
			$this->config = \Registry::set( new ConfigurationParser("config.json") );

			$route = ((isset($_GET["route"])) && ($_GET["route"] != '')) ? $_GET["route"] : $this->config->get("default_route");
			$this->args = explode('/', ltrim($route, '/'));

			$this->view = $this->arg(0);
		}

		/**
		* Get an argument from the url. ommit $argIndex to get all arguments passed with the request.
		* This is set to a string because if the variable passed to this function 
		* is null it would be easier to debug with a false rather than getting the whole array.
		* @param (int) $argIndex the index or the url arg.
		* @return string, or false on failure.
		*/
		public function arg($argIndex = "all") {
			if($argIndex === "all") {
				return $this->args;
			} elseif(isset($this->args[$argIndex])) {
				return $this->args[$argIndex];
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
		* Get the path to a template file, ommit .tpl.php extension
		* TODO: cut .tpl.php from the $tpl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) $tpl name of the template file to get path for,
		* @return string
		*/
		public function getViewPath($tpl = null) {
			if($tpl === null) {
				$tpl = $this->view;
			}

			return $this->getThemePath().'/'.basename($tpl).".tpl.php";
		}

		/**
		* Get path to the specified controller file. Ommit the .php extension
		* TODO: cut .php from the $ctrl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) name of the controller file.
		* @return mixed
		*/
		public function getControllerPath($ctrl = null) {
			if($ctrl === null) {
				$ctrl = $this->arg(0);
			}

			if(is_file($this->getApplicationPath()."/application/controllers/".basename($ctrl).".php")) {
				return $this->getApplicationPath()."/application/controllers/".basename($ctrl).".php";
			} else {
				return false;
			}
		}
		
		/**
		* If allowed by configurations controllers can define their own view file to be used.
		* @param Name of the view to use, without .tpl.php extensions.
		* @return bool
		*/
		public function setView($viewName) {
			$this->view = $viewName;
			return true;
		}

		/**
		* Get the path to the current active theme.
		* TODO: Provide a way to get client side theme path.
		* @return string
		*/
		public function getThemePath() {
			return $this->getApplicationPath()."/application/themes/".$this->config->get("theme");
		}

		/**
		* Dispatches the given controller, serves a notfound if it doesn't exists
		* @return array
		*/
		public function dispatch() {
			$base = preg_replace('/\W+/','',strtolower(strip_tags($this->arg(0))));

			if($this->getControllerPath($base) === false) {
				$base = "notfound";
			}

			$controller = $base . "Controller";
			$this->controller = new $controller;

			$method = $this->arg(1);

			if(!method_exists($this->controller, $method)) {
				$method = "index";
			}

			$this->controller->$method();

			return $this->controller->getData();
		}
	}
}