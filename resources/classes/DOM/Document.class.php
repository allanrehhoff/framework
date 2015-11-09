<?php
namespace DOM;
	use string;

	class Document {
		private $stylesheets = [];
		private $javascript = [];

		public function __construct() {
			$this->stylesheets["all"] = [];
			$this->javascript["footer"] = [];
		}

		public function addStylesheet($style, $media = "all") {
			$this->stylesheets[$media][] = $style;
		}

		public function addJavascript($script, $region = "footer") {
			$this->javascript[$region][] = $script;
		}

		public function getStylesheets($media = "all") {
			return $this->stylesheets[$media];
		}

		public function getJavascript($region = "footer") {
			return $this->javascript[$region];
		}
	}