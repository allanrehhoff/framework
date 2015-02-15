<?php
	class Helper {
		public static function url($path = '') {
			$base_url = strtok($_SERVER["REQUEST_URI"],'?');
			
			$final_url = $base_url.$path;
			return $final_url;
		}
	}
?>