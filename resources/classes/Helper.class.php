<?php
	class Helper {
		/*
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		*/
		public static function url($path = '') {
			$base_path = ltrim($path, '/');
			$base_url = strtok($_SERVER["REQUEST_URI"],'?');		
			$final_url = $base_url.$base_path;
			return $final_url;
		}
	}
?>