<?php

/**
 * Parses a JSONC file, and lets you access properties using a dot syntax
 * also supports .jsonc files
 */
class Configuration {
	/**
	 * @var \stdClass The resulting configuration object after being successfully parsed.
	 */
	private \stdClass $parsedConfig;

	/**
	 * @var string Path to the configuration file being parsed.
	 */
	private string $configurationFile;

	/**
	 * The constructor starts parsing of the configuration file.
	 *
	 * ```
	 * <?php
	 * // Example: Initialize Configuration object with a file path
	 * $iConfgiration = new \Configuration(STORAGE . '/config/config.jsonc');
	 * ```
	 * 
	 * @param string|null $configurationFile Absolute filesystem path to a .jsonc file
	 * @throws \Core\Exception\FileNotFound If file does not exist on filesystem.
	 * 
	 */
	public function __construct(?string $configurationFile = null) {
		if ($configurationFile !== null) {
			$this->parse($configurationFile);
		}
	}

	/**
	 * The actual parsing.
	 *
	 * @param string $configurationFile Absolute filesystem path to a .jsonc file
	 * @throws \Core\Exception\FileNotFound If file does not exist on filesystem.
	 * @return void
	 */
	private function parse(string $configurationFile): void {
		if (!is_file($configurationFile)) {
			throw new \Core\Exception\FileNotFound("The given configuration file '" . $configurationFile . "' could not be located.");
		}

		$this->configurationFile = $configurationFile;

		$jsonConfig = file_get_contents($configurationFile);
		$jsonConfig = preg_replace(
			'~(" (?:\\\\. | [^"])*+ ") | \# [^\v]*+ | // [^\v]*+ | /\* .*? \*/~xs',
			'$1',
			$jsonConfig
		);
		$this->parsedConfig = json_decode($jsonConfig, null, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 * Recursively replace {{variables}} in configuration values
	 *
	 * @param mixed $configValue The config value in which to replace variables.
	 * @return mixed
	 */
	protected function replaceVariables(mixed $configValue): mixed {
		if (is_string($configValue) === true) {
			/*
				\{{2}      (match 2 literal {)
				(          (start capture group)
					[^{^}]+   (1+ non {} characters)
				)          (end capture group)
				\}{2}      (match 2 literal })
			*/
			preg_match_all("/\{{2}([^{^}]+)\}{2}/", $configValue, $variables);

			$allowedFunctions = ["getenv", "constant", "defined", "ini_get"];

			for ($i = 0; $i < count($variables[0]); $i++) {
				$token = strtok($variables[1][$i], "(");

				if (in_array($token, $allowedFunctions) === true && $this->has($token) === false) {
					$configValue = eval("return " . $variables[1][$i] . ';');
				} else {
					$configValue = str_replace($variables[0][$i], $this->get($token), $configValue);
				}
			}
		} elseif (is_object($configValue) === true) {
			foreach ($configValue as $key => $value) {
				$configValue->{$key} = $this->replaceVariables($value);
			}
		} elseif (is_array($configValue) === true) {
			foreach ($configValue as $key => $value) {
				$configValue[$key] = $this->replaceVariables($value);
			}
		}

		return $configValue;
	}

	/**
	 * Gets a single configuration value.
	 *
	 * If no configuration setting name is provided, the whole configuration object will be returned.
	 * Sub-values can be accessed using a dot syntax.
	 * 
	 * ```php
	 * // Example: Get a single configuration value
	 * $value = $iConfiguration->get('some.key');
	 * echo $value;
	 *
	 * ```php
	 * // Example: Get the whole configuration object
	 * $config = $iConfiguration->get();
	 * ```
	 *
	 * @param string|null $key The name of the configuration to get value from.
	 * @return mixed|null null on failure.
	 * @throws \InvalidArgumentException If key does not exist in parsed json.
	 */
	public function get(?string $key = null): mixed {
		if ($key === null) {
			return $this->parsedConfig;
		}

		$paths = explode('.', $key);
		$configValue = $this->parsedConfig;

		foreach ($paths as $path) {
			if (!property_exists($configValue, $path)) {
				throw new \InvalidArgumentException($key . " is not a valid configuration");
			}

			$configValue = $configValue->$path;
		}

		$configValue = $this->replaceVariables($configValue);

		return $configValue;
	}

	/**
	 * Test if current config holds a value for a given key
	 * 
	 * ```
	 * // Example: Check if a key exists in the configuration
	 * $exists = $iConfgiration->has('some.key');
	 * var_dump($exists); // bool(true) or bool(false)
	 * ```
	 *
	 * @param string $key The config key to test
	 * @return bool
	 */
	public function has(string $key): bool {
		try {
			$this->get($key);
			return true;
		} catch (\InvalidArgumentException) {
			return false;
		}
	}

	/**
	 * Remove a configuration value.
	 * 
	 * ```php
	 * // Example: Delete a configuration value
	 * $iConfiguration->delete('some.key');
	 * ```
	 *
	 * @param string $key Key of the setting to delete.
	 * @throws \InvalidArgumentException If key does not exist in parsed json.
	 * @return self
	 */
	public function delete(string $key): Configuration {
		$configValue = $this->parsedConfig;

		$paths = explode('.', $key);
		$unsetKey = array_slice($paths, -1)[0];

		foreach ($paths as $path) {
			if (!isset($configValue->$path)) {
				throw new \InvalidArgumentException($key . " is not a valid configuration");
				return false;
			}

			if (isset($configValue->$unsetKey)) {
				unset($configValue->$unsetKey);
			} else {
				$configValue = &$configValue->$path;
			}
		}

		return $this;
	}

	/**
	 * Alias for \Configuration::delete()
	 * 
	 * Example: Delete a configuration value using the remove method
	 * ```php
	 * $iConfgiration->remove('some.key');
	 * ```
	 *
	 * @param string $key Key of the setting to delete.
	 * @see \Configuration::delete() For removing entries
	 * @return \Configuration value of ConfigurationParser::delete()
	 */
	public function remove(string $key): Configuration {
		return $this->delete($key);
	}

	/**
	 * Dynamically set a configuration setting to a given value.
	 * 
	 * ```php
	 * // Example: Set a configuration value dynamically
	 * $iConfgiration->set('new.setting', 'new value');
	 * ```
	 *
	 * @param string $setting Key of the setting.
	 * @param mixed $value Value of $setting
	 * @return \Configuration
	 */
	public function set(string $setting, mixed $value): Configuration {
		$paths = explode('.', $setting);
		$result = &$this->parsedConfig;

		$countedPaths = count($paths);

		foreach ($paths as $i => $path) {
			if ($i < $countedPaths - 1) {
				if (!isset($result->$path)) {
					$result->$path = new \stdClass();
				}

				$result = &$result->$path;
			} else {
				$result->$path = $value;
			}
		}

		return $this;
	}

	/**
	 * @param string $name Name of the property being set
	 * @param mixed $value Value of the property being set
	 * @return void
	 */
	public function __set(string $name, mixed $value): void {
		$this->set($name, $value);
	}

	/**
	 * Again please use the ->get() and ->set() methods
	 *
	 * @see \Configuration::set() For setting config values dynamically
	 * @param string $name Name of a property being retrieved
	 * @return mixed
	 */
	public function __get(string $name): mixed {
		return $this->get($name);
	}

	/**
	 * Get debug information by printing the configuration object.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return "<pre>" . print_r($this->get(), true) . "</pre>";
	}
}
