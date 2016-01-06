<?php
namespace Core {
	use Exception;
	
	/**
	* The mmain class for your application.
	* Core\Application has the responsibility of bootstrapping the framework.
	* Consult the README file for usage examples throughtout the framework.
	* @see README.md
	*/
	class Application {
		private $cwd, $args;
		
		public function __construct() {
			$this->initialize();
		}
		
		/**
		* Use this method to debug your runtime configurations and arguments within the framework.
		* @return string
		*/
		public function __toString() {
			$ob = ob_start();
			Debug::pre($this->args);
			Debug::pre($this->config);
			return ob_get_clean();
		}

		/**
		* Does the actual initialization.
		* TODO: Consider other ways of creating DB connection and parse the configuration file, to abide by the one class, one responsibility standard.
		* @return self
		*/
		private function initialize() {
			\Registry::set("config", new \Core\ConfigurationParser());
			\Registry::set("database", new \Database\DbConnection(
				\Registry::get("config")->get("database.host"),
				\Registry::get("config")->get("database.name"),
				\Registry::get("config")->get("database.username"),
				\Registry::get("config")->get("database.password"),
				\Registry::get("config")->get("database.debug")
			));
			\Registry::set("document", new \DOM\Document);
			
			$this->cwd = getcwd();

			$route = ((isset($_GET["route"])) && ($_GET["route"] != '')) ? $_GET["route"] : \Registry::get("config")->get("default_route");
			$this->args = explode('/', ltrim($route, '/'));
			
			return $this;
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
		public function getTemplatePath($tpl) {
			return $this->getThemePath().'/'.basename($tpl).".tpl.php";
		}

		/**
		* Get path to the specified controller file. Ommit the .php extension
		* TODO: cut .php from the $ctrl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) name of the controller file.
		* @return string
		*/
		public function getControllerPath($ctrl) {
			return $this->getApplicationPath()."/app/controller/".basename($ctrl).".php";
		}
		
		/**
		* Get the path to the current active theme.
		* TODO: Provide a way to get client side theme path.
		* @return string
		*/
		public function getThemePath() {
			return $this->getApplicationPath()."/app/themes/".\Registry::get("config")->get("theme");
		}
	}
}