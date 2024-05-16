<?php

class Environment {
	/**
	 * Constructor for the Environment class.
	 *
	 * @param null|string $environmentFile The path to the environment file (e.g., '.env').
	 */
	public function __construct(?string $environmentFile = null) {
		if ($environmentFile !== null && file_exists($environmentFile) === true) {
			static::parse($environmentFile);
		}
	}

	/**
	 * Parse the environment file and populate the environment variables.
	 *
	 * @param string $environmentFile The path to the environment file (e.g., '.env').
	 * @return void
	 */
	private function parse(string $environmentFile): void {
		$contents = file_get_contents($environmentFile);

		/*
			^:				Match the beginning of a line.
			(\h*#.*\R*)+	This part matches one or more lines that start with #, possibly preceded by whitespace.
				\h* 		Matches zero or more horizontal whitespace characters (e.g., spaces or tabs).
				# 			Matches the # character.
				.* 			Matches any characters after the #.
				\R* 		Matches zero or more line breaks (handles different line ending styles on various platforms).
			m: 				This flag makes ^ match the beginning of each line.
		*/
		$variables = preg_replace("/^(\h*#.*\R*)+/m", '', $contents);
		$variables = parse_ini_string($contents, true);

		foreach ($variables as $var => $value) {
			$this->put($var, $value);
		}
	}

	/**
	 * Set an environment variable with a given name and value.
	 * All names given will be converted to uppercase automatically.
	 *
	 * @param string $name The name of the environment variable.
	 * @param int|float|string|array $value The value of the environment variable.
	 * @return void
	 */
	public function put(string $name, int|float|string|array $value): void {
		$name = strtoupper($name);

		if (is_array($value)) {
			foreach ($value as $key => $value2) {
				$newName = $name . '.' . $key;
				$this->put($newName, $value2);
			}
		} else {
			$keys = explode('.', $name);
			$env = &$_ENV;

			while (count($keys) > 1) {
				$key = array_shift($keys);

				if (!isset($env[$key]) || !is_array($env[$key])) {
					$env[$key] = [];
				}

				$env = &$env[$key];
			}

			$env[array_shift($keys)] = $value;

			putenv($name . '=' . $value);
		}
	}

	/**
	 * Get the value of an environment variable.
	 * Variables may be accessed using dot notations.
	 * If no variable by the given name was found in
	 * the local, set by the operating system or putenv, scope, 
	 * fallback to the value in $_ENV, or false if not found.
	 *
	 * @param string $name The name of the environment variable to retrieve.
	 * @return mixed The value of the environment variable or false if not found.
	 */
	public function get(string $name): mixed {
		$result = getenv($name, true);

		// Maybe it was not found in the global vars
		// Likely to happen if the value is an array
		// e.g. the name of a section was passed in
		if ($result === false) {
			$keys = explode('.', $name);
			$result = $_ENV;

			foreach ($keys as $key) {
				if (!isset($result[$key])) {
					throw new \InvalidArgumentException($name . " is not a valid environment variable");
				}

				$result = $result[$key];
			}
		}

		return $result;
	}
}
