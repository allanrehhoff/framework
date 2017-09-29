<?php
	/**
	* Sets error handling and other useful PHP configurations, to help you write proper code.
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/

	// Version check
	if(version_compare(PHP_VERSION, 5.4, '<')) die("PHP >= 5.4 is required for this framework to function properly.");

	// Error reporting
	ini_set("display_errors", "On");
	error_reporting(E_ALL);

	// Helper constants
	define("CR", "\r");
	define("LF", "\n");
	define("TAB", "\t");
	define("CRLF", CR.LF);
	define("BR", "<br />");
	define("CLI", php_sapi_name() == "cli");
	define("SSL", !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
	define("CWD", getcwd());

	// Exception handler
	set_exception_handler(function($exception) {
		if(CLI) die($exception->getMessage()."(".$exception->getCode().") thrown in "-$exception->getFile().":".$exception->getLine());
		
		$stack = '';
		$stack .= '<ol style="margin-top:0px; line-height:10px;">'."\n";
		
		$trace = array_reverse($exception->getTrace());
		foreach($trace as $item) {
			$stack .= '<li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' line ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
		}
		
		$stack .= '</ol>'."\n";
		
		print '<pre style="">';
		print '<h1 style="margin:0px;">Uncaught Exception: '.get_class($exception).'</h1><br>';
		print '<strong>Code: </strong>'.$exception->getCode().'<br>';
		print '<strong>File: </strong>'.$exception->getFile().'<br>';
		print '<strong>Line: </strong>'.$exception->getLine().'<br>';
		print '<strong>Message: </strong>'.$exception->getMessage().'<br>';
		print '<strong>Stacktrace: </strong><br>'.$stack;
		print '</pre>';
	});

	// Error handler
	set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
		if(!(error_reporting() & $errno)) {
			return;
		}

		switch($errno) {
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				$type = 'Fatal notice';
				$fatal = true;
				break;
			case E_WARNING      :
			case E_USER_WARNING :
				$type = 'Warning';
				$fatal = true;
				break;
			default             :
				$type = 'Fatal error';
				$fatal = true;
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
			print '<pre class="alert alert-danger" style="">' . "\n";
			print '<p style="line-height:10px; margin:0px;">Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n"."</p>";
			print '  <ol style="margin-top:0px; line-height:10px;">' . "\n";
			
			foreach($trace as $item) {
				print "<li>" . (isset($item["file"]) ? $item["file"] : "<unknown file>")." line ".(isset($item["line"]) ? $item["line"] : "<unknown line>")." calling ".$item['function']."()</li>" . "\n";
			}
			
			print "  </ol>" . "\n";
			print "</pre>" . "\n";
		}
		
		if(ini_get("log_errors")) {
			$items = [];
			foreach($trace as $item) {
				$items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item["line"]) ? $item["line"] : '<unknown line>')." calling ".$item["function"]."()";
			}
			
			$message = "Backtrace from ".$type." '".$errstr ."' at ".$errfile.' '.$errline.": ".implode(" | ", $items);
			error_log($message);
		}
		
		if($fatal) {
			exit(-1);
		}
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

	// Include paths.
	set_include_path(dirname(__FILE__));