<?php
	/**
	* Sets error handling and other useful PHP configurations, to help you write proper code.
	*/

	// Error reporting
	ini_set("display_errors", "On");
	error_reporting(E_ALL);

	// Exception handler
	set_exception_handler(function($iException) {
		$stacktrace = [];
		$trace = array_reverse($iException->getTrace());

		foreach($trace as $item) {
			$stacktrace[] = (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item["function"]."()".LF;
		}

		if(IS_CLI) {
			print "Uncaught ".get_class($iException) . ':'.LF;
			print "Message: ".$iException->getMessage().LF;
			print "Code: ".$iException->getCode().LF;
			print "File: ".$iException->getFile().LF;
			print "Line: ".$iException->getLine().LF;
			print "Stacktrace: ".LF;
			print TAB.implode(LF, $stacktrace);
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
						"class" => get_class($iException),
						"message" => $iException->getMessage(),
						"code" => $iException->getCode(),
						"file" => $iException->getFile(),
						"line" => $iException->getLine()
					],
					"trace" => $stacktrace
				]);
			} else {
				print "<div style=\"font-family: monospace; background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 15px 15px;\">
							<h1 style=\"margin:0px;\">Uncaught \\".get_class($iException)."</h1>".BR."
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
	set_error_handler(function($errno, $errstr, $errfile, $errline) {
		if(!(error_reporting() & $errno)) {
			return;
		}

		$error = $errstr . " in file " . $errfile . " on line " . $errline;
		throw new \ErrorException($error, $errno, E_ERROR, $errfile, $errline);
	});

	// Cache headers
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// Helper constants
	define("CR", "\r");
	define("LF", "\n");
	define("TAB", "\t");
	define("SPACE", " ");
	define("CRLF", CR.LF);
	define("BR", "<br />");
	define("DS", DIRECTORY_SEPARATOR);
	define("IS_SSL", !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
	define("IS_CLI", php_sapi_name() == "cli");
	define("APP_PATH", __DIR__);
	define("STORAGE", realpath(APP_PATH."/../storage"));
	define("ACCEPT_JSON", !IS_CLI && (str_contains(strtolower((getallheaders()["Accept"] ?? "*/*")), "application/json")));

	// Include paths
	set_include_path(APP_PATH);

	// Default timezone
	date_default_timezone_set("Europe/Copenhagen");

	// Autoloader
	spl_autoload_register(function($className) {

		$controllerClass = "Controller";
		$className = str_replace("\\", "/", $className);

		if($className != $controllerClass && str_ends_with($className, $controllerClass)) {
			$classFile = APP_PATH."/controllers/".substr($className, 0, -strlen($controllerClass)).".php";

			if(file_exists($classFile) !== true) {
				throw new \Core\Exception\FileNotFound;
			}

			require $classFile;
		} else {
			$classFile = APP_PATH."/classes/".$className.".php";
			require $classFile;
		}
	});