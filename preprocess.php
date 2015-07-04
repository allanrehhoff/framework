<?php
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	header('Pragma: no-cache');
	header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
	header('Expires: -1');

	define('CR', "\r");
	define('LF', "\n");
	define('CRLF', CR.LF);
	define('TAB', "\t");
	define('BR', '<br />');

	spl_autoload_register(function($class_name) {
		$class_name = str_replace('\\', '/', $class_name);
		
		$class_file = getcwd().'/resources/classes/'.$class_name.'.class.php';

		if(file_exists($class_file)) {
			include $class_file;
		}
	});
	
	set_exception_handler(function($exception) {
		if(!error_reporting()) {
			return;
		}
		
		if(php_sapi_name() == 'cli') {
			die($exception);
		}
		
		$stack = '';
		$stack .= '<ol style="margin-top:0px; line-height:10px;">'."\n";
		
		$trace = array_reverse($exception->getTrace());
		foreach($trace as $item) {
			$stack .= '<li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' line ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
		}
		
		echo '</ol>'."\n";
		
		echo '<pre style="">';
		echo '<h1 style="margin:0px;">Uncaught Exception: '.get_class($exception).'</h1><br>';
		echo '<strong>Code: </strong>'.$exception->getCode().'<br>';
		echo '<strong>File: </strong>'.$exception->getFile().'<br>';
		echo '<strong>Line: </strong>'.$exception->getLine().'<br>';
		echo '<strong>Message: </strong>'.$exception->getMessage().'<br>';
		echo '<strong>Stacktrace: </strong><br>'.$stack;
		echo '</pre>';
	});
	
	set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
		
		if(!(error_reporting() & $errno)) {
			return;
		}
			
		switch($errno) {
			case E_STRICT       :
			case E_NOTICE       :
			case E_USER_NOTICE  :
				// Spaghetti? not in this case..
				$type = 'fatal notice';
				$fatal = true;
				break;
			case E_WARNING      :
			case E_USER_WARNING :
				$type = 'warning';
				$fatal = false;
				break;
			default             :
				$type = 'fatal error';
				$fatal = true;
				break;
		}
		
		$trace = array_reverse(debug_backtrace());
		array_pop($trace);
		
		if(php_sapi_name() == 'cli') {
			echo 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n";
			foreach($trace as $item) {
				echo '  ' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' line ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()' . "\n";
			}
		} else {
			echo '<pre class="alert alert-danger" style="">' . "\n";
			echo '<p style="line-height:10px; margin:0px;">Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ':' . "\n".'</p>';
			echo '  <ol style="margin-top:0px; line-height:10px;">' . "\n";
			
			foreach($trace as $item) {
				echo '<li>' . (isset($item['file']) ? $item['file'] : '<unknown file>') . ' line ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()</li>' . "\n";
			}
			
			echo '  </ol>' . "\n";
			echo '</pre>' . "\n";
		}
		
		if(ini_get('log_errors')) {
			$items = array();
			foreach($trace as $item) {
				$items[] = (isset($item['file']) ? $item['file'] : '<unknown file>') . ' ' . (isset($item['line']) ? $item['line'] : '<unknown line>') . ' calling ' . $item['function'] . '()';
			}
			
			$message = 'Backtrace from ' . $type . ' \'' . $errstr . '\' at ' . $errfile . ' ' . $errline . ': ' . join(' | ', $items);
			error_log($message);
		}
		
		if($fatal) {
			exit(1);
		}
	});
	
?>