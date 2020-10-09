<?php
	class Url {
		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @author Allan Thue Rehhoff
		* @return string
		*/
		public static function fromUri(string $uri = '') : string {
			$protocol = SSL ? "https://" : "http://";
			$host  = $_SERVER['HTTP_HOST'];
			$self = dirname($_SERVER['PHP_SELF']);

			if(\Registry::get("Core\Configuration")->get("enable_i18n") === true) {
				$langcode = \Registry::get("Core\Language")->getLangcode();
				$self = $self.$langcode;
			}

			$path = rtrim($self, '/\\');
			$baseurl = $protocol.$host.$path."/";
			return $baseurl.ltrim($uri, '/');
		}

		/**
		* Perform a redirect with a X-Redirect-By header
		* @param (string) $location Location of the redirect
		* @param (string) $xRedirectBy Human readable indidcator of who performed this redirect
		* @return void
		*/
		public static function redirect(string $location, string $xRedirectBy) : void {
			header("HTTP/1.1 302 Found");
			header("Cache-Control: no-cache, must-revalidate");
			header("X-Redirect-By: " . $xRedirectBy);
			header("Location: " . $location);
			exit;
		}
	}
?>