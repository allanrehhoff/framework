<?php
namespace DOM;

	class Document {
		private static $stylesheets = [];
		private static $javascript = [];

		public function __construct() {
			$stylesheets["all"] = [];
			$javascript["footer"] = [];
		}

		public static function addStylesheet($style, $media = "all") {
			self::$stylesheets[$media][] = $style;
		}

		public static function addJavascript($script, $region = "footer") {
			self::$javascript[$region][] = $script;
		}

		public static function getStylesheets($media = "all") {
			return self::$stylesheets[$media];
		}

		public static function getJavascript($region = "footer") {
			return self::$javascript[$region];
		}
	}