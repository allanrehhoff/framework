<?php
namespace Application;
	use Exception;
	
	class Initialize {
		
		protected $title, $config;
		public $args;
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
			$this->loadConfiguration();

			$themeFunctions = $this->getThemePath().'/functions.php';
			if(is_file($themeFunctions)) {
				include $themeFunctions;
			}
			
			$route = ( (isset($_GET['route']) ) && ($_GET['route'] != '') ) ? $_GET['route'] : $this->config('default_route');

			$this->setArgs($route);
	
			$this->setTitle();
			
			return $this;
		}

		private function loadConfiguration() {
			//require $this->getApplicationPath().'/config.php';
			
			//$this->config = (object) $config;
		
			$jsonConfig = file_get_contents($this->getApplicationPath().'/config.json');
			$this->config = json_decode($jsonConfig);
			
			return $this;
		}

		//this is set to a string because if the variable passed to this function 
		//is null it would be easier to debug with a false rather than getting the whole array.
		public function arg($arg_index = 'all') {
			if($arg_index === 'all') {
				return $this->args;
			} elseif(isset($this->args[$arg_index])) {
				return $this->args[$arg_index];
			}
			return false;
		}

		public function config($conf = null) {
			if($conf === null) {
				return $this->config;
			}
			
			$paths = explode('.', $conf);
			
			$configValue = $this->config;
			foreach ($paths as $path) {
				if(!isset($configValue->$path)) {
					Throw new Exception($conf.' is not a valid configuration');
					return false;
				}
				$configValue = $configValue->$path;
			}
			
			return $configValue;
			
			//return false;
		}

		public function setConfig($setting, $value) {
			$paths = explode('.', $setting);
			$result = &$this->config;
			
			$countedPaths = count($paths)
			foreach ($paths as $i => $path) {
				if ($i < $countedPaths-1) {
					if (!isset($result->$path)) {
						$result->$path = new stdClass();
					}
					$result = &$result->$path;
				} else {
					$result->$path = $value;
				}
			}
			return $this;
		}

		public function setArgs($route) {
			$this->args = explode('/', ltrim($route, '/'));
			return $this;
		}
		
		public function setTitle($title = '') {
			$this->title = sprintf($this->config->base_title, $title);
			return $this;
		}

		public function getTitle() {
			return $this->title;
		}

		public function getApplicationPath() {
			return $this->cwd;
		}
		
		public function getTemplatePath($tpl) {
			return $this->getThemePath().'/'.$tpl.'.tpl.php';
		}
		
		public function getControllerPath($ctrl) {
			return $this->getApplicationPath().'/resources/controller/'.$ctrl.'.php';
		}
		
		public function getThemePath() {
			return $this->getApplicationPath().'/resources/themes/'.$this->config->theme;
		}
	}
?>