<?php
	class Application {
		
		protected $title;
		public $args;

		public function __construct($init = true) {
			if($init === true) {
				$this->loadConfiguration();
				$this->Initialize();
			}
		}

		private function Initialize() {
			$this->setTitle();
			
			$themeFunctions = $this->getThemePath().'/functions.php';
			if(is_file($themeFunctions)) {
				include($themeFunctions);
			}

			return $this;
		}

		private function loadConfiguration() {
			require(getcwd().'/config.php');
			
			$this->config = (object) $config;

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

		public function config($value = 'all') {
			if($value === 'all') {
				return $this->config;
			} elseif(isset($this->config->{$value})) {
				return $this->config->{$value};
			}
			return false;
		}

		public function setConfig($setting, $value) {
			$this->config->{$setting} = $value;
			return $this;
		}

		public function setArgs($route) {
			$this->args = explode('/', $route);
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
			return getcwd();
		}
		
		public function getTemplatePath($tpl) {
			return $this->getThemePath().'/'.$tpl.'.tpl.php';
		}
		
		public function getControllerPath($ctrl) {
			return $this->getApplicationPath().'/resources/controller/'.$ctrl.'.php';
		}
		
		public function getThemePath() {
			return getcwd().'/resources/themes/'.$this->config->theme;
		}
	}
?>