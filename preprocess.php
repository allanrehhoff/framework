<?php
	/**
	* Sets error handling and other useful PHP configurations, to help you write proper code.
	* @author Allan Thue Rehhoff
	*/

	// Version check
	if(version_compare(PHP_VERSION, 5.4, '<')) die("PHP >= 5.4 is required for this framework to function properly.");

	// Error reporting
	ini_set("display_errors", "On");
	error_reporting(E_ALL);

	// Include paths.
	set_include_path(dirname(__FILE__));

	// Default timezone
	date_default_timezone_set("Europe/Copenhagen");

	// Helper constants
	define("CR", "\r");
	define("LF", "\n");
	define("TAB", "\t");
	define("CRLF", CR.LF);
	define("BR", "<br />");
<<<<<<< HEAD
	define("SSL", !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	
=======
	define("CLI", php_sapi_name() == "cli");
	define("SSL", !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	define("CWD", getcwd());

	// Exception handler
>>>>>>> v3
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
			print "<div class=\"alert alert-danger\" style=\"font-family: 'Courier New';\">
					    <h1 style=\"margin:0px;\">Uncaught Exception: ".get_class($exception)."</h1>".BR."
						<strong>Code: </strong>".$exception->getCode().BR."
						<strong>File: </strong>".$exception->getFile().BR."
						<strong>Line: </strong>".$exception->getLine().BR."
						<strong>Message: </strong>".$exception->getMessage().BR."
						<strong>Stacktrace: </strong>".BR."
						<ol style=\"margin-top:0px; line-height:10px;\">".LF."
							<li>".implode("</li><li>", $stacktrace)."</li>
						</ol>
					</div>";
		}
	});

	// Error handler
	set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
		if(!(error_reporting() & $errno)) {
			return;
		}

		// Be super nazi about errors, eliminates most noob mistakes.
		switch($errno) {
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				$type = 'Fatal Notice';
				break;
			case E_WARNING      :
			case E_USER_WARNING :
				$type = 'Fatal Warning';
				break;
			default             :
				$type = 'Fatal Error';
				break;
		}

		$trace = array_reverse(debug_backtrace());
		array_pop($trace);
		
		if(CLI) {
			print "Backtrace from ".$type." '".$errstr."' at ".$errfile.' '.$errline.':'."\n";
			foreach($trace as $item) {
				print '  ' .(isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item['function']."()"."\n";
			}
		} else {
			print "<pre class=\"alert alert-danger\">".LF;
			print "<p style=\"line-height:10px; margin:0px;\">Backtrace from ".$type." '".$errstr."' at ".$errfile.' '.$errline.':'.LF."</p>";
			print "  <ol style=\"margin-top:0px; line-height:10px;\">".LF;
			
			foreach($trace as $item) {
				print "<li>" . (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item['function']."()</li>" . "\n";
			}
			
			print "  </ol>" . "\n";
			print "</pre>" . "\n";
		}
		
		if(ini_get("log_errors")) {
			$items = [];
			foreach($trace as $item) {
				$items[] = (isset($item["file"]) ? $item["file"] : "<unknown file>") . ' ' . (isset($item["line"]) ? $item["line"] : '<unknown line>')." calling ".$item["function"]."()";
			}
			
			$message = "Backtrace from ".$type." '".$errstr ."' at ".$errfile.' '.$errline.": ".implode(" | ", $items);
			error_log($message);
		}
		
		exit(-1);
	});

	// Autoloader
	spl_autoload_register(function($className) {
		$className = str_replace("\\", "/", $className);

		if(substr($className, -10) == "Controller" && $className != "Core/Controller") {
			$classFile = CWD."/application/controllers/".substr($className, 0, -10).".php";
		} else {
			$classFile = CWD."/application/classes/".$className.".class.php";
		}

		require $classFile;
	});