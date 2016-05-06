<?php
/**
* @package Rehhoff_Framework
*/
	class Tools {
		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @return string
		*/
		public static function url($path = '') {
			$basePath = ltrim($path, '/');
			$baseUrl = strtok($_SERVER["REQUEST_URI"],'?');		
			$finalUrl = $baseUrl.$basePath;
			return $finalUrl;
		}
	}
?>