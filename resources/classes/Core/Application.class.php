<?php
namespace Core;
	use Exception;
	
	class Application {
		
		protected $title;
		public $args, $config, $db;
		private $cwd;
		
		public function __construct($init = true) {
			if($init === true) {
				$this->initialize();
			}
		}
		
		public function __toString() {
			$ob = ob_start();
			Debug::pre($this->args);
			Debug::pre($this->config);
			return ob_get_clean();
		}

		private function initialize() {
			$this->cwd = getcwd();
			
			$this->title = '';

			$this->config = new ConfigurationParser();
			/*$this->db = new \Database\DbConnection(
				$this->config->get("database.host"),
				$this->config->get("database.name"),
				$this->config->get("database.username"),
				$this->config->get("database.password"),
				$this->config->get("database.debug")
			);*/

			$themeFunctions = $this->getThemePath()."/functions.php";
			if(is_file($themeFunctions)) {
				include $themeFunctions;
			}
			
			$route = ((isset($_GET["route"])) && ($_GET["route"] != '')) ? $_GET["route"] : $this->config->get("default_route");

			$this->args = explode('/', ltrim($route, '/'));
			
			return $this;
		}

		//this is set to a string because if the variable passed to this function 
		//is null it would be easier to debug with a false rather than getting the whole array.
		public function arg($argIndex = "all") {
			if($argIndex === "all") {
				return $this->args;
			} elseif(isset($this->args[$argIndex])) {
				return $this->args[$argIndex];
			}
			return false;
		}
		
		public function setTitle($title) {
			$this->title = $title;
			return $this;
		}

		public function getTitle() {
			if(trim($this->title) != '') {
				$title = sprintf($this->config->get("base_title"), $this->title);
			} else {
				$title = sprintf($this->config->get("base_title"), str_replace("- ", '', $this->config->get("base_title")));
			}
			return $title;
		}

		public function getApplicationPath() {
			return $this->cwd;
		}
		
		public function getTemplatePath($tpl) {
			return $this->getThemePath().'/'.basename($tpl).".tpl.php";
		}
		
		public function getControllerPath($ctrl) {
			return $this->getApplicationPath()."/resources/controller/".basename($ctrl).".php";
		}
		
		public function getThemePath() {
			return $this->getApplicationPath()."/resources/themes/".$this->config->get("theme");
		}
	}
?>