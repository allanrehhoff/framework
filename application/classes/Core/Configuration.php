<?php
namespace Core {
	use Exception;
	
	/**
	* Handles parsing and saving of a given configuration file.
	* @extends Singleton
	* @author Allan Thue Rehhoff
	*/
	class Configuration {
		private $parsedConfig, $configurationFile;
		public $error = '';
		
		/**
		* The constructor starts parsing of the configuration file.
		*/
		public function __construct($configurationFile) {
			$this->configurationFile = $configurationFile;
			$this->parse($configurationFile);
		}

		/**
		* The actual parsing.
		* @throws ConfigurationParserException
		* @return void
		*/
		private function parse(string $configurationFile) : void {
			if(!is_file($configurationFile)) {
				throw new ConfigurationException("The given configuration file '$configurationFile' could not be located.");
			}
			
			$jsonConfig = file_get_contents($configurationFile);
			$this->parsedConfig = json_decode($jsonConfig, null, 512, JSON_THROW_ON_ERROR);
		}

		/**
		 * Recursively replace {{variables}} in configuration values
		 * @param $configValue The config value in which to replace variables.
		 * @return mixed
		 */
		protected function replaceVariables($configValue) {
			if(is_string($configValue) === true) {
				/*
					\{{2}      (match 2 literal {)
					(          (start capture group)
					 [^{^}]+   (1+ non {} characters)
					)          (end capture group)
					\}{2}      (match 2 literal })
				*/
				$variables = preg_match_all("/\{{2}([^{^}]+)\}{2}/", $configValue, $matches);

				for ($i = 0; $i < count($matches[0]); $i++) { 
					$configValue = str_replace($matches[0][$i], $this->get($matches[1][$i]), $configValue);
				}
			} else if(is_object($configValue) === true) {
				foreach($configValue as $key => $value) {
					$configValue->{$key} = $this->replaceVariables($value);
				}
			} else if(is_array($configValue) === true) {
				foreach($configValue as $key => $value) {
					$configValue[$key] = $this->replaceVariables($value);
				}
			}

			return $configValue;
		}

		/**
		* Gets a single configuration value.
		* If no configuration setting name is provided, the whole configuration object will be returned.
		* Sub-values can be accessed using a dot syntax.
		* @param (string) $conf The name of the configuration to get value from.
		* @return mixed, null on failure.
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
					return null;
				}

				$configValue = $configValue->$path;
			}

			$configValue = $this->replaceVariables($configValue);
			
			return $configValue;
		}

		/**
		* Remove a configuration value
		* @param (string) $conf Key of the setting to delete.
		* @throws ConfigurationParserException
		* @return self
		*/
		public function delete(string $conf) : Configuration {
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
		public function remove(string $conf) : Configuration {
			return $this->delete($conf);
		}

		/**
		* Dynamically set a configuration setting to a given value.
		* @param $setting Key of the setting.
		* @param $value Value of $setting
		* @return self
		*/
		public function set(string $setting, $value) : Configuration {
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
		public function save() : Configuration {
			$jsonConfig = json_encode($this->get(), JSON_PRETTY_PRINT);
			file_put_contents($this->configurationFile, $jsonConfig);
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