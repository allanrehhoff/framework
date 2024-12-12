<?php

/**
 * Simple logger class.
 *
 * Log entries can be added with any of the following methods:
 *  - Logger::info($message, $title = '' )      // an informational message intended for the user
 *  - Logger::debug($message, $title = '' )     // a diagnostic message intended for the developer
 *  - Logger::warning($message, $title = '' )   // a warning that something might go wrong
 *  - Logger::error($message, $title = '' )     // explain why the program is going to crash
 *
 */
class Logger {

	/**
	 * Incremental log, where each entry is an array with the following elements:
	 *
	 *  - timestamp => timestamp in seconds as returned by time()
	 *  - level => severity of the bug; one between debug, info, warning, error
	 *  - name => name of the log entry, optional
	 *  - message => actual log message
	 * @var array
	 */
	protected static array $log = [];

	/**
	 * Whether to print log entries to screen as they are added.
	 * @var bool
	 */
	public static bool $printLog = true;

	/**
	 * Whether to write log entries to file as they are added.
	 * @var bool
	 */
	public static bool $writeLog = false;

	/**
	 * Directory where the log will be dumped, without the final slash; default
	 * is this file's directory
	 * @var string
	 */
	public static string $logDir = __DIR__;

	/**
	 * File name for the log saved in the log dir
	 * @var string
	 */
	public static string $logFileName = "application";

	/**
	 * File extension for the logs saved in the log dir
	 * @var string
	 */
	public static string $logFileExtension = "log";

	/**
	 * Whether to append to the log file (true) or to overwrite it (false)
	 * @var bool
	 */
	public static bool $logFileAppend = true;

	/**
	 * Set the maximum level of logging to write to logs
	 * @var string
	 */
	public static string $logLevel = "error";

	/**
	 * Name for the default timer
	 * @var string
	 */
	public static string $defaultTimer = "timer";

	/**
	 * Map logging levels to syslog specifications, there's room for the other levels
	 * @var array
	 */
	private static array $logLevelIntegers = [
		'debug' => 7,
		'info' => 6,
		'warning' => 4,
		'error' => 3
	];

	/**
	 * Absolute path to the log file, built at run time
	 * @var string
	 */
	private static string $logFilePath = '';

	/**
	 * Where should we write/print the output to? Built at run time
	 * @var array
	 */
	private static array $outputStreams = [];

	/**
	 * Whether the init() function has already been called
	 * @var bool
	 */
	private static bool $loggerReady = false;

	/**
	 * Associative array used as a buffer to keep track of timed logs
	 * @var array
	 */
	private static array $timeTracking = [];

	/**
	 * Add a log entry with a diagnostic message for the developer.
	 * 
	 * @param string $message The log message
	 * @param string $name Prepended to log message
	 * @return bool|object
	 */
	public static function debug(string $message, string $name = ''): bool|object {
		return static::add($message, $name, 'debug');
	}

	/**
	 * Add a log entry with an informational message for the user.
	 * 
	 * @param string $message The log message
	 * @param string $name Prepended to log message
	 * @return bool|object
	 */
	public static function info(string $message, string $name = ''): bool|object {
		return static::add($message, $name, 'info');
	}

	/**
	 * Add a log entry with a warning message.
	 * 
	 * @param string $message The log message
	 * @param string $name Prepended to log message
	 * @return bool|object
	 */
	public static function warning(string $message, string $name = ''): bool|object {
		return static::add($message, $name, 'warning');
	}

	/**
	 * Add a log entry with an error.
	 * usually followed by script termination.'
	 * 
	 * @param string $message The log message
	 * @param string $name Prepended to log message
	 * @return bool|object
	 */
	public static function error(string $message, string $name = ''): bool|object {
		return static::add($message, $name, 'error');
	}

	/**
	 * Start counting time, using $name as an identifier.
	 *
	 * Returns the start time or false if a time tracker with the same name
	 * exists
	 * 
	 * @param null|string $name Name of the timer
	 * @return bool|float Current timestamp in microseconds, false if the timer with the same name is already started.
	 */
	public static function time(null|string $name = null): bool|float {
		if ($name === null) {
			$name = static::$defaultTimer;
		}

		if (!isset(static::$timeTracking[$name])) {
			static::$timeTracking[$name] = microtime(true);
			return static::$timeTracking[$name];
		} else {
			return false;
		}
	}

	/**
	 * Stop counting time, and create a log entry reporting the elapsed amount of
	 * time.
	 *
	 * Returns the total time elapsed for the given time-tracker, or false if the
	 * time tracker is not found.
	 * 
	 * @param null|string $name Name of the timer
	 * @param int $decimals Number of decimal places in the elapsed time
	 * @param string $level Log level
	 * @return float|bool Total time elapsed for the given time-tracker, or false if the time tracker is not found.
	 */
	public static function timeEnd(null|string $name = null, int $decimals = 6, string $level = 'debug') {
		$isDefaultTimer = $name === null;

		if ($isDefaultTimer) {
			$name = static::$defaultTimer;
		}

		if (isset(static::$timeTracking[$name])) {
			$start = static::$timeTracking[$name];
			$end = microtime(true);
			$elapsedTime = number_format(($end - $start), $decimals);

			unset(static::$timeTracking[$name]);

			if (!$isDefaultTimer) {
				static::add($elapsedTime . " seconds", "Elapsed time for '" . $name . "'", $level);
			} else {
				static::add($elapsedTime . " seconds", "Elapsed time", $level);
			}

			return $elapsedTime;
		} else {
			return false;
		}
	}

	/**
	 * Add an entry to the log.
	 *
	 * This function does not update the pretty log. 
	 * 
	 * @param string $message The log message
	 * @param string $name Prepended to log message
	 * @param string $level Log level
	 * @return bool|object
	 */
	private static function add(string $message, string $name = '', string $level = 'debug'): bool|object {
		/* Check if the logging level severity warrants writing this log */
		if (static::$logLevelIntegers[$level] > static::$logLevelIntegers[static::$logLevel]) {
			return false;
		}

		/* Create the log entry */
		$logEntry = (object) [
			"timestamp" => time(),
			"name" => $name,
			"message" => $message,
			"level" => $level,
		];

		/* Initialize the logger if it hasn't been done already */
		static::init();

		/* Add the log entry to the incremental log */
		static::$log[] = $logEntry;

		/* Write the log to output, if requested */
		if (count(static::$outputStreams) > 0) {
			$outputLine = static::formatLogEntry($logEntry) . PHP_EOL;

			foreach (static::$outputStreams as $key => $stream) {
				fputs($stream, $outputLine);
			}
		}

		return $logEntry;
	}

	/**
	 * Take one log entry and return a one-line human-readable string
	 * 
	 * @param object $logEntry Log entry
	 * @return string
	 */
	public static function formatLogEntry(object $logEntry): string {
		$logLine = "";

		if (!empty($logEntry)) {
			/* Make sure the log entry is stringified */
			foreach ($logEntry as $key => $value) {
				$logEntry->$key = print_r($value, true);
			}

			/* Build a line of the pretty log */
			$logLine .= date('c', $logEntry->timestamp) . " ";
			$logLine .= "[" . strtoupper($logEntry->level) . "] : ";

			if (!empty($logEntry->name)) {
				$logLine .= $logEntry->name . " => ";
			}

			$logLine .= $logEntry->message;
		}

		return $logLine;
	}

	/**
	 * Determine whether and where the log needs to be written; executed only
	 * once.
	 *
	 * @return array - An associative array with the output streams. The 
	 * keys are 'output' for STDOUT and the filename for file streams.
	 */
	public static function init(): array {
		if (!static::$loggerReady) {
			/* Build log file path */
			if (file_exists(static::$logDir)) {
				static::$logFilePath = implode(DIRECTORY_SEPARATOR, [static::$logDir, static::$logFileName]);

				if (!empty(static::$logFileExtension)) {
					static::$logFilePath .= "." . static::$logFileExtension;
				}
			}

			/* Print to screen */
			if (static::$printLog === true) {
				static::$outputStreams["stdout"] = STDOUT;
			}

			/* Print to log file */
			if (static::$writeLog === true) {
				if (file_exists(static::$logDir)) {
					$mode = static::$logFileAppend ? "a" : "w";
					static::$outputStreams[static::$logFilePath] = fopen(static::$logFilePath, $mode);
				}
			}

			/* Now that we have assigned the output stream, this function does not need
			to be called anymore */
			static::$loggerReady = true;
		}

		return static::$outputStreams;
	}

	/**
	 * Dump the whole log to the given file.
	 *
	 * Useful if you don't know beforehand the name of the log file. Otherwise,
	 * you should use the real-time logging option, that is, the $writeLog or
	 * $printLog options.
	 *
	 * The method formatLogEntry() is used to format the log.
	 *
	 * @param string $filePath Absolute path of the output file. If empty, will use the class property $logFilePath
	 * @return void
	 */
	public static function dumpToFile(string $filePath = ''): void {
		if (!$filePath) {
			$filePath = static::$logFilePath;
		}

		if (file_exists(dirname($filePath))) {
			$mode = static::$logFileAppend ? "a" : "w";
			$outputFile = fopen($filePath, $mode);

			foreach (static::$log as $logEntry) {
				$logLine = static::formatLogEntry($logEntry);
				fwrite($outputFile, $logLine . PHP_EOL);
			}

			fclose($outputFile);
		}
	}

	/**
	 * Dump the whole log to a string and return it.
	 *
	 * The method formatLogEntry() is used to format the log.
	 * 
	 * @return string
	 */
	public static function dumpToString(): string {
		$output = '';

		foreach (static::$log as $logEntry) {
			$logLine = static::formatLogEntry($logEntry);
			$output .= $logLine . PHP_EOL;
		}

		return $output;
	}

	/**
	 * Empty the log
	 * @return void
	 */
	public static function clearLog(): void {
		static::$log = [];
	}
}
