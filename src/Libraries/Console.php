<?php

/**
 * Common useful tools for command line operations
 */
class Console {
	/**
	 * Show a status bar in the console
	 * 
	 * ```php
	 * // Example: Show a status bar
	 * \Console::showStatus($done, $total);
	 * ```
	 *
	 * @param int $done   How many items are completed
	 * @param int $total  How many items are to be done total
	 * @param int $size   Optional size of the status bar
	 * @return void
	 */
	public static function showStatus(int $done, int $total, int $size = 30): void {
		static $startTime;

		// if we go over our bound, just ignore it
		if ($done > $total) return;
		if (empty($startTime)) $startTime = time();

		$now = time();

		$percent = (float)($done / $total);

		$bar = intval($percent * $size);

		$statusBar = "\r[";
		$statusBar .= str_repeat("=", $bar);

		if ($bar < $size) {
			$statusBar .= ">";
			$statusBar .= str_repeat(" ", $size - $bar);
		} else {
			$statusBar .= "=";
		}

		$disp = number_format($percent * 100, 0);
		$statusBar .= "] " . $disp . "%  " . $done . "/" . $total;

		$remaining = $total - $done;
		$elapsed = $now - $startTime;

		$statusBar .= "; remaining: " . number_format($remaining) . "; time elapsed: " . number_format($elapsed) . " sec.";

		print $statusBar . "  ";
		flush();
	}

	/**
	 * Iterates over an array calling $callback on each item while maintaining a nice progressbar
	 * 
	 * ```php
	 * // Example: Process an array with a progress bar
	 * \Console::progress($iterable, function(mixed $key, mixed $value): void {
	 *     // Do whatever with $key and $value
	 * });
	 * ```
	 * 
	 * @param array $iterable The array to process
	 * @param array|callable $callback 	A callback to apply to each item in the array
	 * 									Array key will be sent as first argument
	 * 									Value as second argument
	 * @return void
	 */
	public static function progress(array $iterable, array|callable $callback): void {
		set_time_limit(0);

		$i = 0;
		$total = count($iterable);

		if ($total == 0) {
			print "No rows to process";
			return;
		}

		self::showStatus($i, $total);

		foreach ($iterable as $key => $item) {
			$callback($key, $item);
			$i++;

			self::showStatus($i, $total);
		}

		print CRLF;
	}

	/**
	 * Prints a confirmation prompt to the console
	 * This will exit the program if confirm is not positive.
	 * 
	 * ```php
	 * // Example: Prompt for confirmation
	 * \Console::confirm('Are you sure?');
	 * ```
	 * @param string $message The message to preset to the user
	 * @return void
	 */
	public static function confirm(string $message): void {
		$message .= " [y/n] ";

		print $message;
		$input = strtolower(trim(fgets(STDIN)));

		// Set the cursor upwards and remove previous line
		print chr(27) . "[1A";
		print str_repeat(SPACE, (mb_strlen($message) + mb_strlen($input))) . CR;

		if (!str_starts_with($input, 'y')) {
			exit;
		}
	}

	/**
	 * Viable alternative to PHP's getopt(); which aborts upon first unknown option passed
	 * @param array $args Array of command line arguments typically args vector (argv)
	 * @return stdClass containing array of parsed options and flags
	 * @see https://www.php.net/manual/en/features.commandline.php#83843
	 */
	public static function arguments(array $args): \stdClass {
		array_shift($args);
		$endofoptions = false;

		$ret = [
			'commands' => [],
			'options' => [],
			'flags'		=> [],
			'arguments' => [],
		];

		while ($arg = array_shift($args)) {
			// if we have reached end of options,
			//we cast all remaining argvs as arguments
			if ($endofoptions) {
				$ret['arguments'][] = $arg;
				continue;
			}

			// Is it a command? (prefixed with --)
			if (substr($arg, 0, 2) === '--') {
				// is it the end of options flag?
				if (!isset($arg[3])) {
					$endofoptions = true; // end of options;
					continue;
				}

				$value = "";
				$com  = substr($arg, 2);

				// is it the syntax '--option=argument'?
				if (strpos($com, '=')) {
					list($com, $value) = explode("=", $com, 2);
				} elseif (isset($args[0]) && preg_match('/^-.*/', $args[0]) !== 1) {
					// is the option not followed by another option but by arguments
					// while(strpos($args[0], '-') !== 0) $value .= array_shift($args).' ';
					$value = array_shift($args);
					// $value = rtrim($value, ' ');
				}

				$value = !empty($value) ? $value : true;

				if (isset($ret['options'][$com])) {
					if (is_array($ret['options'][$com])) {
						$ret['options'][$com][] = $value;
					} else {
						$ret['options'][$com] = [$ret['options'][$com], $value];
					}
				} else {
					$ret['options'][$com] = $value;
				}

				continue;
			}

			// Is it a flag or a serial of flags? (prefixed with -)
			if (substr($arg, 0, 1) === '-') {
				for ($i = 1; isset($arg[$i]); $i++) $ret['flags'][] = $arg[$i];
				continue;
			}

			// finally, it is not option, nor flag, nor argument
			$ret['commands'][] = $arg;
			continue;
		}

		if (!count($ret['options']) && !count($ret['flags'])) {
			$ret['arguments'] = array_merge($ret['commands'], $ret['arguments']);
			$ret['commands'] = [];
		}

		return (object)$ret;
	}

	/**
	 * Get value from the command line args 'options' array
	 * 
	 * ```php
	 * // Example: Get a value from command line options
	 * $value = \Console::getOption('dry-run');
	 * ```
	 * 
	 * @param string $option Name of the option to get, typically the part after '--'
	 * 						 fx. --only-missing would be 'only-missing'
	 * @return mixed
	 */
	public static function getOption(string $option): mixed {
		return self::arguments($GLOBALS["argv"])->options[$option] ?? null;
	}
}
