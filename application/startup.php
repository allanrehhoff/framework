<?php
	/**
	* Sets error handling and other useful PHP configurations, to help you write proper code.
	* @author Allan Thue Rehhoff
	*/

	// Cache headers
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	// Helper constants
	define("CR", "\r");
	define("LF", "\n");
	define("TAB", "\t");
	define("CRLF", CR.LF);
	define("BR", "<br />");
	define("SSL", !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on');
	define("CLI", php_sapi_name() == "cli");
	define("APP", __DIR__);
	define("DS", DIRECTORY_SEPARATOR);
	define("STORAGE", realpath("../storage"));

	// Output buffering
	// The ob_gzhandler callback returns false if browser doesn't support gzip
	//!CLI ? ob_start("ob_gzhandler") ? : ob_start() : false;

	// Error reporting
	ini_set("display_errors", "On");
	error_reporting(E_ALL);

	// Include paths.
	set_include_path(dirname(__FILE__));

	// Default timezone
	date_default_timezone_set("Europe/Copenhagen");

	// Exception handler
	set_exception_handler(function($exception) {
		$stacktrace = [];
		$trace = array_reverse($exception->getTrace());

		foreach($trace as $item) {
			$stacktrace[] = (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item["function"]."()".LF;
		}

		if(CLI) {
			print "Uncaught Exception: ".get_class($exception).LF;
			print "Code: ".$exception->getCode().LF;
			print "File: ".$exception->getFile().LF;
			print "Line: ".$exception->getLine().LF;
			print "Message: ".$exception->getMessage().LF;
			print "Stacktrace: ".LF;
			print TAB.implode(TAB, $stacktrace);
		} else {
			while(ob_get_length()) ob_end_clean();

			if(headers_sent() === false) {
				header("HTTP/1.1 503 Service Temporarily Unavailable");
				header("Retry-After: 3600");
			}

			print "<div style=\"font-family: monospace; background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 15px 15px;\">
					    <h1 style=\"margin:0px;\">Uncaught Exception: ".get_class($exception)."</h1>".BR."</h1>
						<strong>Code: </strong>".$exception->getCode().BR."
						<strong>File: </strong>".$exception->getFile().BR."
						<strong>Line: </strong>".$exception->getLine().BR."
						<strong>Message: </strong>".$exception->getMessage().BR."
						<strong>Stacktrace: </strong>".BR."
						<ol style=\"margin-top:0px;\">".LF."
							<li>".implode("</li><li>", $stacktrace)."</li>
						</ol>
					</div>";
		}
	});

	// Error handler
	// Be super conservative about errors, eliminates most noob mistakes.
	set_error_handler(function($errno, $errstr, $errfile, $errline) {
		if(!(error_reporting() & $errno)) {
			return;
		}

		$error = $errstr . ' in file ' . $errfile . ' on line ' . $errline;
		throw new ErrorException($error, $errno, E_ERROR, $errfile, $errline);
	});

	// Autoloader
	spl_autoload_register(function($className) {
		$className = str_replace("\\", "/", $className);

		$classFile = APP."/classes/".$className.".php";
		if(!file_exists($classFile) && substr($className, -10) == "Controller") {
			$classFile = APP."/controllers/".substr($className, 0, -10).".php";
		}

		require $classFile;
	});