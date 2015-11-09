<?php
namespace Core;
	use Exception;
	
	class ConfigurationParser {
		private $parsedConfig;
		private $configurationFile = 'config.json';
		public $error = '';
		
		/*
		* The constructor parses the configuration file.
		*/
		public function __construct() {
			if(!is_file(getcwd().'/config.json')) {
				throw new ConfigurationParserException('configuration file could not be located, please create config.json in current working directory.');
			}
			
			$jsonConfig = file_get_contents(getcwd().'/'.$this->configurationFile);
			$this->parsedConfig = json_decode($jsonConfig);

			$jsonErrorMap = array(
				JSON_ERROR_DEPTH => "Maximum stack depth exceeded.",
				JSON_ERROR_STATE_MISMATCH => "Underflow or the modes mismatch.",
				JSON_ERROR_CTRL_CHAR => "Unexpected control character found.",
				JSON_ERROR_SYNTAX => "Syntax error, your configuration file contains malformed JSON.",
				JSON_ERROR_UTF8 => "Your configuration file may me incorrectly encoded, it contains malformed UTF-8 characters."
			);

			$this->error = json_last_error();

			if($this->error !== JSON_ERROR_NONE) {
				if(isset($jsonErrorMap[$this->error])) {
					throw new ConfigurationParserException("Unable to parse configuration file, ".$jsonErrorMap[$this->error]);
				} else {
					throw new ConfigurationParserException("Unable to parse configuration file, unknown error.");
				}
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
					throw new ConfigurationParserException($conf." is not a valid configuration");
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
					throw new ConfigurationParserException($conf." is not a valid configuration");
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
			return "<pre>".print_r($this->get(), true)."</pre>";
		}
	}