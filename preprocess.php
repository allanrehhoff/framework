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
	define("CWD", __DIR__);
	define("DS", DIRECTORY_SEPARATOR);

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
	// Be super nazi about exception, eliminates most noob mistakes.
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
			if(ob_get_length()) ob_clean();

			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Retry-After: 3600");

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
	// Be super nazi about errors, eliminates most noob mistakes.
	set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
		if(!(error_reporting() & $errno)) {
			return;
		}

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
			print "Backtrace from ".$type." '".$errstr."' at ".$errfile.' '.$errline.':'.LF;
			foreach($trace as $item) {
				print '  ' .(isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item['function']."()".LF;
			}
		} else {
			if(ob_get_length()) ob_clean();

			header("HTTP/1.1 503 Service Temporarily Unavailable");
			header("Retry-After: 3600");

			print "<pre style=\"background-color: #f44336; border-color: #f32c1e; color:#FFF; padding: 15px 15px 0px 15px;\">".LF;
			print "<strong style=\"line-height:10px; margin:0px;\">Backtrace from ".$type." '".$errstr."' at ".$errfile.' '.$errline.':'.LF."</strong>";
			print "<ol style=\"margin-top:0px; line-height:10px; margin-bottom:0px;\">".LF;
			
			foreach($trace as $item) {
				print "<li>" . (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item['function']."()</li>".LF;
			}
			
			print "</ol>".LF;
			print "</pre>".LF;
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

		$classFile = CWD."/application/classes/".$className.".php";
		if(!file_exists($classFile) && substr($className, -10) == "Controller") {
			$classFile = CWD."/application/controllers/".substr($className, 0, -10).".php";
		}

		/*
		if(substr($className, -10) == "Controller" && $className != "Core/Controller") {
			$classFile = CWD."/application/controllers/".substr($className, 0, -10).".php";
		} else {
			$classFile = CWD."/application/classes/".$className.".php";
		}
		*/

		require $classFile;
	});