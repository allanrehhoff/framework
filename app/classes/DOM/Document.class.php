<?php
namespace DOM {	
	/**
	* Central class for setting javascripts and stylesheets to be rendered.
	*/
	class Document {
		private static $stylesheets = [];
		private static $javascript = [];
		protected $title = '';

		public function __construct() {
			self::$stylesheets["all"] = [];
			self::$javascript["footer"] = [];
		}

		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @return string
		*/
		public static function url($path = '') {
			$base_path = ltrim($path, '/');
			$base_url = strtok($_SERVER["REQUEST_URI"],'?');		
			$final_url = $base_url.$base_path;
			return $final_url;
		}

		/**
		* Add a stylesheet to be linked.
		* @return voidDocument
		* @param (string) $style Valid path to the stylesheet. Watch out for casing and whitespaces when using Document::getStylesheets();
		* @param (string) $media Media query this stylesheet should apply to.
		* @return void
		*/
		public static function addStylesheet($style, $media = "all") {
			self::$stylesheets[$media][] = $style;
		}

		/**
		* Add a javascript file to be rendered.
		* @param (string) $script Valid path to the javascript file.
		* @param (string) $region Region of the DOM where this javascript should be rendered.
		* @return void
		*/
		public static function addJavascript($script, $region = "footer") {
			self::$javascript[$region][] = $script;
		}

		/**
		* Get current stylesheets to be linked.
		* @param $media Only return stylesheets in this media query.
		* @return array
		*/
		public static function getStylesheets($media = "all") {
			return self::$stylesheets[$media];
		}

		/**
		* Get current javascript files to be rendered.
		* @param (string) $region Only return javascript files belonging to this region.
		* @return array
		*/
		public static function getJavascript($region = "footer") {
			return self::$javascript[$region];
		}
	}
}