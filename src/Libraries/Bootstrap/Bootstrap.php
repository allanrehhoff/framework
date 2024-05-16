<?php

namespace Bootstrap;

/**
 * Class Bootstrap
 *
 * This class is instantiated by the applications main entry point.
 * usually that is 'index.php', and must not be instantiated after.
 * 
 * Only alter the contents of this class if you have mission-critical
 * logic, that needs to be available for every process, even so consider
 * using event listeners in \Bootstrap\Events instead.
 */
class Bootstrap {
	/**
	 * Initializes the application.
	 * @return void
	 */
	public function startup(): void {
		!defined("APP_PATH") or throw new \RuntimeException("Attempt to bootstrap, when application is already running.");

		$this->registerConstants();
		$this->registerAutoloaders();
		$this->registerErrorHandlers();
		$this->registerEventListeners();
	}

	/**
	 * Registers application constants.
	 * @return void
	 */
	private function registerConstants(): void {
		define("CR", "\r");
		define("LF", "\n");
		define("TAB", "\t");
		define("CRLF", CR . LF);
		define("SPACE", " ");
		define("BR", "<br />");
		define("DS", DIRECTORY_SEPARATOR);
		define("IS_SSL", !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
		define("IS_CLI", PHP_SAPI === "cli");
		define("APP_PATH", realpath(__DIR__ . "/../../"));
		define("STORAGE", realpath(APP_PATH . "/../storage"));
		define("ACCEPT_JSON", !IS_CLI && (str_contains(strtolower((getallheaders()["Accept"] ?? "*/*")), "application/json")));
	}

	/**
	 * Registers autoloader functions.
	 * @return void
	 */
	private function registerAutoloaders(): void {
		spl_autoload_register(
			function (string $className): void {
				$controllerClass = \Controller::class;
				$className = str_replace("\\", "/", $className);

				if ($className != $controllerClass && str_ends_with($className, $controllerClass)) {
					$classFile = APP_PATH . "/Controllers/" . $className . ".php";
				} else {
					$classFile = APP_PATH . "/Libraries/" . $className . ".php";
				}

				if (file_exists($classFile) === true) {
					require $classFile;
				} else {
					throw new \Core\Exception\FileNotFound;
				}
			}
		);

		$vendorAutoload = [
			APP_PATH . "/vendor/autoload.php",
			APP_PATH . "/../vendor/autoload.php"
		];

		foreach ($vendorAutoload as $autoloadFile) {
			$autoloadFile = realpath($autoloadFile);

			if (file_exists($autoloadFile)) {
				require $autoloadFile;
				break;
			}
		}
	}

	/**
	 * Registers error handlers.
	 * Any PHP error will be be converted to \ErrorException and thrown.
	 * 
	 * @return void
	 */
	private function registerErrorHandlers(): void {
		set_error_handler(
			function (int $errno, string $errstr, string $errfile, int $errline): bool {
				// Support suppressing errors with '@'
				if (!(error_reporting() & $errno)) {
					return false;
				}

				$error = $errstr . " in file " . $errfile . " on line " . $errline;
				throw new \ErrorException($error, $errno, E_ERROR, $errfile, $errline);
			}
		);

		set_exception_handler(
			function (\Throwable $iException): void {
				// Exception handling logic
				$stacktrace = [];
				$trace = array_reverse($iException->getTrace());

				foreach ($trace as $item) {
					$stacktrace[] = (isset($item["file"]) ? $item["file"] : "<unknown file>") . " line " . (isset($item["line"]) ? $item["line"] : "<unknown line>") . " calling " . $item["function"] . "()" . LF;
				}

				if (IS_CLI) {
					// CLI error response
					print "Uncaught " . $iException::class . ':' . LF .
						"Message: " . $iException->getMessage() . LF .
						"Code: " . $iException->getCode() . LF .
						"File: " . $iException->getFile() . LF .
						"Line: " . $iException->getLine() . LF .
						"Stacktrace: " . LF .
						TAB . implode(LF, $stacktrace);
				} else {
					// Web error response
					while (ob_get_length()) ob_end_clean();

					if (headers_sent() === false) {
						header("HTTP/1.1 503 Service Temporarily Unavailable");
						header("Retry-After: 3600");
					}

					if (ACCEPT_JSON) {
						// JSON error response
						header("Content-Type: application/json");
						print json_encode([
							"status" => "error",
							"data" => [
								"class" => $iException::class,
								"message" => $iException->getMessage(),
								"code" => $iException->getCode(),
								"file" => $iException->getFile(),
								"line" => $iException->getLine()
							],
							"trace" => $stacktrace
						]);
					} else {
						// HTML error response
						print "<div style=\"font-family: monospace; background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 15px 15px;\">
										<h1 style=\"margin:0px;\">Uncaught \\" . $iException::class . "</h1>" . BR . "
										<strong>Message: </strong>" . $iException->getMessage() . BR . "
										<strong>Code: </strong>" . $iException->getCode() . BR . "
										<strong>File: </strong>" . $iException->getFile() . BR . "
										<strong>Line: </strong>" . $iException->getLine() . BR . "
										<strong>Stacktrace: </strong>" . BR . "
										<ol style=\"margin-top:0px;\">" . LF . "
											<li>" . implode("</li><li>", $stacktrace) . "</li>
										</ol>
									</div>";
					}
				}

				exit(1);
			}
		);
	}

	/**
	 * Registers event listeners.
	 * @return void
	 */
	private function registerEventListeners(): void {
		(new EventService)->registerDefaultListeners();
	}
}
