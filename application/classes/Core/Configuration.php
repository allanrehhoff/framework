<?php
namespace Core {
	use Exception;
	
	/**
	* Handles parsing and saving of a given configuration file.
	* @extends Singleton
	* @author Allan Thue Rehhoff
	*/
	class Configuration {
		private $parsedConfig;
		public $error = '';
		
		/**
		* The constructor starts parsing of the configuration file.
		*/
		public function __construct($configurationFile) {
			$this->parseConfig($configurationFile);
		}

		/**
		* The actual parsing.
		* @throws ConfigurationParserException
		* @return void
		*/
		private function parseConfig($configurationFile) {
			if(!is_file($configurationFile)) {
				throw new ConfigurationException("The given configuration file '$configurationFile' could not be located.");
			}
			
			$jsonConfig = file_get_contents($configurationFile);
			$this->parsedConfig = json_decode($jsonConfig);

			$this->error = json_last_error();

			if($this->error !== JSON_ERROR_NONE) {
				$jsonErrorMap = [
					JSON_ERROR_DEPTH => "Maximum stack depth exceeded.",
					JSON_ERROR_STATE_MISMATCH => "Underflow or the modes mismatch.",
					JSON_ERROR_CTRL_CHAR => "Unexpected control character found.",
					JSON_ERROR_SYNTAX => "Syntax error, your configuration file contains malformed JSON.",
					JSON_ERROR_UTF8 => "Your configuration file may me incorrectly encoded, it contains malformed UTF-8 characters."
				];

				if(isset($jsonErrorMap[$this->error])) {
					throw new ConfigurationException("Unable to parse configuration file, ".$jsonErrorMap[$this->error]);
				} else {
					throw new ConfigurationException("Unable to parse configuration file, unknown error.");
				}
			}
		}

		/**
		* Gets a single configuration value.
		* If no configuration setting name is provided, the whole configuration object will be returned.
		* Sub-values can be accessed using a dot syntax.
		* @param (string) $conf The name of the configuration to get value from.
		* @return mixed, false on failure.
		* @throws ConfigurationParserException
		*/
		public function get($conf = null) {
			if($conf === null) {
				return $this->parsedConfig;
			}
			
			$paths = explode('.', $conf);
			$configValue = $this->parsedConfig;
			foreach ($paths as $path) {
				if(!isset($configValue->$path)) {
					throw new ConfigurationException($conf." is not a valid configuration");
					return false;
				}
				$configValue = $configValue->$path;
			}
			
			return $configValue;
		}
		
		/**
		* Remove a configuration value
		* @param (string) $conf Key of the setting to delete.
		* @throws ConfigurationParserException
		* @return self
		*/
		public function delete($conf) {
			$paths = explode('.', $conf);
			$configValue = $this->parsedConfig;
			foreach ($paths as $path) {
				if(!isset($configValue->$path)) {
					throw new ConfigurationException($conf." is not a valid configuration");
					return false;
				}
			}
			
			unset($configValue->$path);
			
			return $this;
		}
		
		/**
		* Alias for ConfigurationParser::delete()
		* @return value of onfigurationParser::delete()
		*/
		public function remove($conf) {
			return $this->delete($conf);
		}
		
		/**
		* Dynamically set a configuration setting to a given value.
		* @param $setting Key of the setting.
		* @param $value Value of $setting
		* @return self
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
		
		/**
		* Permanently save the current runtime configuration.
		* @return self
		*/
		public function save() {
			$jsonConfig = json_encode($this->get(), JSON_PRETTY_PRINT);
			file_put_contents(getcwd().'/'.$this->configurationFile, $jsonConfig);
			return $this;
		}
		
		/**
		* I have no idea how this reacts if $user->does->this = 'stupid';
		* But doing so is discouraged, and at some point I might introduce Exceptions here.
		* @return value of ConfigurationParser::set();
		*/
		public function __set($name, $value) {
			return $this->set($name, $value);
		}
		
		/**
		* Again please use the ->get() and ->set() methods
		* @see ConfigurationParser::__set();
		* @return mixed
		*/
		public function __get($name) {
			return $this->get($name);
		}
		
		/**
		* Get debug information by printing the configuration object.
		* @return string
		*/
		public function __toString() {
			return "<pre>".print_r($this->get(), true)."</pre>";
		}
	}
}