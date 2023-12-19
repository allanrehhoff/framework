<?php
	/**
	* Sets error handling and other useful PHP configurations, to help you write proper code.
	*/

	// Helper constants
	define("CR", "\r");
	define("LF", "\n");
	define("TAB", "\t");
	define("SPACE", " ");
	define("CRLF", CR.LF);
	define("BR", "<br />");
	define("DS", DIRECTORY_SEPARATOR);
	define("IS_SSL", !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
	define("IS_CLI", PHP_SAPI === "cli");
	define("APP_PATH", realpath(__DIR__ . "/../"));
	define("STORAGE", realpath(APP_PATH."/../storage"));
	define("ACCEPT_JSON", !IS_CLI && (str_contains(strtolower((getallheaders()["Accept"] ?? "*/*")), "application/json")));

	// Autoloader
	spl_autoload_register(function(string $className) {
		$controllerClass = \Controller::class;
		$className	 = str_replace("\\", "/", $className);

		if($className != $controllerClass && str_ends_with($className, $controllerClass)) {
			$classFile = APP_PATH."/Controllers/".$className.".php";

			if(file_exists($classFile) !== true) {
				throw new \Core\Exception\FileNotFound;
			}
		} else {
			$classFile = APP_PATH."/Libraries/".$className.".php";
		}

		// NOTE:
		// Do not remove the file_exists(); check, it'll break
		// the PHPunit testsuite, by throw the erro
		// 'PHPUnit/Composer/Autoload/ClassLoader.php' Failed to open stream
		if(file_exists($classFile) === true) {
			require $classFile;
		}
	});

	// Exception handler
	set_exception_handler(function($iException) {
		$stacktrace = [];
		$trace = array_reverse($iException->getTrace());

		foreach($trace as $item) {
			$stacktrace[] = (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item["function"]."()".LF;
		}

		if(IS_CLI) {
			print "Uncaught " . $iException::class . ':' . LF .
				  "Message: " . $iException->getMessage() . LF .
				  "Code: " . $iException->getCode() . LF .
				  "File: " . $iException->getFile() . LF .
				  "Line: " . $iException->getLine() . LF .
				  "Stacktrace: " . LF .
				TAB . implode(LF, $stacktrace);

		} else {
			while(ob_get_length()) ob_end_clean();

			if(headers_sent() === false) {
				header("HTTP/1.1 503 Service Temporarily Unavailable");
				header("Retry-After: 3600");
			}

			if(ACCEPT_JSON) {
				header("Content-Type: applicattion/json");
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
				print "<div style=\"font-family: monospace; background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 15px 15px;\">
							<h1 style=\"margin:0px;\">Uncaught \\".$iException::class."</h1>".BR."
							<strong>Message: </strong>".$iException->getMessage().BR."
							<strong>Code: </strong>".$iException->getCode().BR."
							<strong>File: </strong>".$iException->getFile().BR."
							<strong>Line: </strong>".$iException->getLine().BR."
							<strong>Stacktrace: </strong>".BR."
							<ol style=\"margin-top:0px;\">".LF."
								<li>".implode("</li><li>", $stacktrace)."</li>
							</ol>
						</div>";
			}
		}
		exit(1);
	});

	// Error handler
	// Be super conservative about errors, eliminates most noob mistakes.
	set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) : bool {
		if(!(error_reporting() & $errno)) {
			return false;
		}

		$error = $errstr . " in file " . $errfile . " on line " . $errline;
		throw new \ErrorException($error, $errno, E_ERROR, $errfile, $errline);
	});

	// Error reporting
	ini_set("display_errors", "On");
	error_reporting(E_ALL);

	// Include paths
	set_include_path(APP_PATH);

	// Default timezone
	date_default_timezone_set("Europe/Copenhagen");

	// Thirdparty
	if(file_exists(APP_PATH."/vendor/autoload.php")) {
		require APP_PATH."/vendor/autoload.php";
	} else if(file_exists(APP_PATH."/../vendor/autoload.php")) {
		require APP_PATH."/../vendor/autoload.php";
	}