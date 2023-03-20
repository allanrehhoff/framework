<?php
	class Url {
		/**
		 * Get the base URL of the current application.
		 *
		 * @return string The base URL of the application.
		 */
		public static function getBaseurl() : string {
			$baseurl = "";
			$protocol = IS_SSL ? "https://" : "http://";
			$domainName = $_SERVER["HTTP_HOST"];
			
			// Add protocol and domain name to base URL
			$baseurl .= $protocol . $domainName;
			
			// Add port number to base URL if it"s not the default
			if($_SERVER["SERVER_PORT"] != ($protocol === "https://" ? 443 : 80)) {
				$baseurl .= ":" . $_SERVER["SERVER_PORT"];
			}
			
			// Add path to base URL
			$baseurl .= rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
			
			return $baseurl;
		}

		/**
		 * Removes any need for having a hardcoded basepath in some obscure place
		 * "cough"wordpress"cough"
		 * @param string $url Path to element of which to create a URI.
		 * @return string
		 */
		public static function fromUri(string $uri = "") : string {
			$baseurl = self::getBaseurl();

			return $baseurl.ltrim($uri, "/");
		}

		/**
		 * Perform a redirect with a X-Redirect-By header
		 * @param string $location Location of the redirect
		 * @param string $xRedirectBy Human readable indidcator of who performed this redirect
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