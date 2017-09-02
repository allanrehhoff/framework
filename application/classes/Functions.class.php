<?php
/**
* @package Rehhoff_Framework
*/
	class Functions {
		/**
		* Helps you debug your application.
		* @author Allan Thue Rehhoff
		*/
		public function __construct() {
		}
		
		/**
		* Pretty print almost any variable in a human readable format.
		* @param (mixed) $stuff A variable to debug
		* @param (boolean) $exit whether to exit the PHP process after printing debug information.
		*/
		public static function pre($stuff, $exit = false) {
			print "<pre style='background:#FFD; clear:both;'>";
			if(count($stuff) > 0 && $stuff !== false) {
				print_r($stuff);
			} else {
				var_dump($stuff);
			}
			print "</pre>";
			
			if($exit) exit;
		}
		
		/**
		* Get an easily readable tree of previously called functions.
		*/
		public static function getCaller() {
			$c = '';
			$file = '';
			$func = '';
			$class = '';
			$trace = debug_backtrace();
			if (isset($trace[2])) {
				$file = $trace[1]["file"];
				$func = $trace[2]["function"];
				if ((substr($func, 0, 7) == "include") || (substr($func, 0, 7) == "require")) {
					$func = '';
				}
			} else if (isset($trace[1])) {
				$file = $trace[1]["file"];
				$func = '';
			}

			if (isset($trace[3]["class"])) {
				$class = $trace[3]["class"];
				$func = $trace[3]["function"];
				$file = $trace[2]["file"];
			} else if (isset($trace[2]["class"])) {
				$class = $trace[2]["class"];
				$func = $trace[2]["function"];
				$file = $trace[1]["file"];
			}
			if ($file != '') $file = basename($file);
			$c = $file . ':';
			$c .= ($class != '') ? ':' . $class . "->" : '';
			$c .= ($func != '') ? $func . "(): " : '';
			
			self::pre($c);
		}

		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @author Allan Thue Rehhoff
		* @return string
		*/
		public static function url($asset = '') {
			$protocol = SSL ? "https://" : "http://";
			$host  = $_SERVER['HTTP_HOST'];
			$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$baseurl = $protocol.$host.$path."/";
			return $baseurl.ltrim($asset, '/');
		}
	}