<?php

namespace Bootstrap;

use \EventListeners\HttpsRedirect;
use \EventListeners\ContentSecurityPolicy;

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

		$this->registerTimezone();
		$this->registerConstants();
		$this->registerAutoloaders();
		$this->registerErrorHandlers();
		$this->registerGlobalObjects();
		$this->registerEventListeners();
	}

	/**
	 * Sets the default timezone to UTC.
	 * @return void
	 */
	private function registerTimezone(): void {
		ini_set("date.timezone", "UTC");
		date_default_timezone_set("UTC");
	}

	/**
	 * Sets global state objects in the registry
	 * @return void
	 */
	private function registerGlobalObjects(): void {
		\Registry::set(new \Configuration(STORAGE . "/config/global.jsonc"));

		\Registry::set(new \Environment(APP_PATH . "/.env"));

		/** @disregard P1011 Do not connect when running tests */
		if (defined('TESTS_RUNNING') && TESTS_RUNNING) return;

		\Registry::set(new \Database\Connection(
			\Registry::getConfiguration()->get("database.host"),
			\Registry::getConfiguration()->get("database.username"),
			\Registry::getConfiguration()->get("database.password"),
			\Registry::getConfiguration()->get("database.name")
		));

		\Registry::set(new \ContentSecurityPolicy\Builder());
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
		spl_autoload_register(function (string $className): void {
			$controllerClass = \Controller::class;
			$className = str_replace("\\", "/", $className);

			// Exclude PHPUnit-related classes
			// Removing this line breaks tests
			// As throwing FileNotFound causes
			// the testsuite to fail silently
			if (str_contains($className, 'PHPUnit')) return;

			if ($className != $controllerClass && str_ends_with($className, $controllerClass)) {
				$classFile = APP_PATH . "/Controllers/" . $className . ".php";
			} else {
				$classFile = APP_PATH . "/Libraries/" . $className . ".php";
			}

			if (file_exists($classFile) === true) {
				require $classFile;
			} else {
				throw new \Core\Exception\FileNotFound(\basename($classFile));
			}
		});

		// Backwards compatibility alias
		class_alias(\Core\Attributes\RespondWith::class, '\Core\Attributes\AllowedContentTypes');

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
		ini_set('log_errors', 1);
		ini_set('error_log', STORAGE . '/logs/php_error.log');

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

		// Exception handler
		// Free'd when an unhandled error happens
		// e.g. out of memory errors.
		$emergencyMemory = str_repeat('*', 1024 * 50);

		set_exception_handler(
			function (\Throwable $iException) use ($emergencyMemory): void {
				// Free up our reserved memory.
				$emergencyMemory = null;
				unset($emergencyMemory);
				gc_collect_cycles();

				error_log($iException->getMessage() . " in file " . $iException->getFile() . " on line " . $iException->getLine());
				error_log("Stacktrace: " . LF . $iException->getTraceAsString());

				if (IS_CLI) {
					// CLI error response
					print "Uncaught " . $iException::class . ':' . LF .
						"Message: " . $iException->getMessage() . LF .
						"Code: " . $iException->getCode() . LF .
						"File: " . $iException->getFile() . LF .
						"Line: " . $iException->getLine() . LF .
						"Stacktrace: " . LF .
						str_replace(["#"], [str_repeat(SPACE, 4) . "#"], $iException->getTraceAsString()) . LF;
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
							"data" => "An unexpected error occurred"
						]);
					} else {
						// HTML error response
						print " <div style=\"font-family: monospace; background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 15px 15px;\">
									<h1 style=\"margin:0px;\">Something unexpected happened, and we couldn't process your request</h1>" . BR . "
									<p>Please try the following:</p>
									<ul>
										<li>Refresh the page or try again at a later time.</li>
										<li>If the problem persists, contact us with the following information:</li>
										<ul>
											<li>A detailed step-by-step guide how you got this error</li>
											<li>What you were trying to do</li>
											<li>What you expected to happen</li>
											<li>This part: " . $_SERVER["REQUEST_URI"] . "</li>
											<li>This time: " . date("Y-m-d H:i:s") . "</li>
										</ul>
									</ul>
								</div>";
					}
				}

				exit(1);
			}
		);
	}

	/**
	 * Define your default event listeners in this function.
	 * These listeners will be added upon every request
	 *
	 * Example event listener class defined elsewhere:
	 *
	 * ```php
	 * <?php
	 * namespace EventListners;
	 *
	 * class UserRegistration {
	 *		public function handle(User $iUser) {
	 *			// Assuming you have the following classes loaded
	 *			\EmailService::sendWelcomeEmail($iUser);
	 *
	 *			\Logger::debug("User registered: " . $iUser->getUsername());
	 *		}
	 * }
	 * ```
	 * Use the fully qualified class name
	 * \Core\Event::addListener("controller.execute.before", \UserRegistration::class);
	 *
	 * Closures may also be passes
	 * \Core\Event::addListener("controller.execute.before", fn(\User $iUser) => \EmailService::sendWelcomeEmail($iUser));
	 *
	 * @return void
	 */
	private function registerEventListeners(): void {
		\Core\Event::addListener("core.global.init", HttpsRedirect::class);
		\Core\Event::addListener("core.output.html", ContentSecurityPolicy::class);
	}
}
