<?php
namespace DOM {	
	/**
	* Central class for handling javascripts and stylesheets to be rendered.
	* @author Allan Thue Rehhoff
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
			return isset(self::$stylesheets[$media]) ? self::$stylesheets[$media] : [];
		}

		/**
		* Get current javascript files to be rendered.
		* @param (string) $region Only return javascript files belonging to this region.
		* @return array
		*/
		public static function getJavascript($region = "footer") {
			return isset(self::$javascript[$region]) ? self::$javascript[$region] : [];
		}
	}
}