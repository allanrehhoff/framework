<?php
namespace Core {
	use Exception;
	
	/**
	* The mmain class for your application.
	* Core\Application has the responsibility of bootstrapping the framework.
	*/
	class Application {
		protected $title;
		public $args, $config, $db;
		private $cwd;
		
		public function __construct() {
			$this->initialize();
		}
		
		/**
		* Use this method to debug your runtime configurations and arguments within the framework.
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
			$this->cwd = getcwd();
			
			$this->title = '';

			$this->config = new ConfigurationParser();
			$this->db = new \Database\DbConnection(
				$this->config->get("database.host"),
				$this->config->get("database.name"),
				$this->config->get("database.username"),
				$this->config->get("database.password"),
				$this->config->get("database.debug")
			);

			$this->document = new \DOM\Document;
			
			$route = ((isset($_GET["route"])) && ($_GET["route"] != '')) ? $_GET["route"] : $this->config->get("default_route");

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
		* Set a dynamic value for the title tag.
		* @param (string) $title a title to display in a template file.
		* @return self
		*/
		public function setTitle($title) {
			$this->title = $title;
			return $this;
		}

		/**
		* Get the current page title to be displayed.
		* @return string
		*/
		public function getTitle() {
			if(trim($this->title) != '') {
				$title = sprintf($this->config->get("base_title"), $this->title);
			} else {
				$title = sprintf($this->config->get("base_title"), str_replace("- ", '', $this->config->get("base_title")));
			}
			return $title;
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
			return $this->getApplicationPath()."/resources/controller/".basename($ctrl).".php";
		}
		
		/**
		* Get the path to the current active theme.
		* TODO: Provide a way to get client side theme path.
		* @return string
		*/
		public function getThemePath() {
			return $this->getApplicationPath()."/resources/themes/".$this->config->get("theme");
		}
	}
}