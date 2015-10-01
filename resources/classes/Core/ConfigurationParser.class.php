<?php
namespace Core;
	use Exception;
	
	class ConfigurationParser {
		private $parsedConfig;
		private $configurationFile = 'config.json';
		
		/*
		* The constructor parses the configuration file.
		*/
		public function __construct() {
			if(!is_file(getcwd().'/config.json')) {
				throw new Exception('configuration file could not be located');
			}
			
			$jsonConfig = file_get_contents(getcwd().'/'.$this->configurationFile);
			$this->parsedConfig = json_decode($jsonConfig);
			
			if($this->parsedConfig == null) {
				throw new Exception('Unable to parse configuration file, perhaps malformed JSON?');
			}
			
			return $this;
		}
		
		/*
		* Gets a single configuration value.
		* If no configuration setting name is provided, 
		* it will return the parsed configuration
		*/
		public function get($conf = null) {
			if($conf === null) {
				return $this->parsedConfig;
			}
			
			$paths = explode('.', $conf);
			$configValue = $this->parsedConfig;
			foreach ($paths as $path) {
				if(!isset($configValue->$path)) {
					Throw new Exception($conf.' is not a valid configuration');
					return false;
				}
				$configValue = $configValue->$path;
			}
			
			return $configValue;
		}
		
		/*
		* Remove a configuration value
		*/
		public function delete($conf) {
			$paths = explode('.', $conf);
			$configValue = $this->parsedConfig;
			foreach ($paths as $path) {
				if(!isset($configValue->$path)) {
					Throw new Exception($conf.' is not a valid configuration');
					return false;
				}
			}
			
			unset($configValue->$path);
			
			return $this;
		}
		
		/*
		* Alias for ->delete() I found myself typing this alot..
		*/
		
		public function remove($conf) {
			$this->delete($conf);
		}
		
		/*
		* Dynamically set a configuration setting to a given value.
		*/
		public function set($setting, $value) {
			$paths = explode('.', $setting);
			$result = &$this->parsedConfig;
			
			$countedPaths = count($paths);
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
		
		public function save() {
			$jsonConfig = json_encode($this->get(), JSON_PRETTY_PRINT);
			var_dump(file_put_contents(getcwd().'/'.$this->configurationFile, $jsonConfig));
		}
		
		/*
		* I have no idea how this reacts if $user->does->this = 'stupid';
		* But doing so is discouraged, and at some point I might introduce Exceptions here,
		*/
		public function __set($name, $value) {
			$this->set($name, $value);
		}
		
		/*
		* Again please use the ->get() and ->set() methods
		*/
		public function __get($name) {
			return $this->get($name);
		}
		
		public function __toString() {
			return '<pre>'.print_r($this->get(), true).'</pre>';
		}
	}