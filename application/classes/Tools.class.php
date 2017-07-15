<?php
/**
* @package Rehhoff_Framework
*/
	class Tools {
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
?>