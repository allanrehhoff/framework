<?php
	/**
	* Central class for handling javascripts and stylesheets to be rendered.
	*/
	class Assets {
		private $stylesheets = [];
		private $javascript = [];
		protected $title = '';

		public function __construct() {
			$this->stylesheets["all"] = [];
			$this->javascript["footer"] = [];
		}

		/**
		* Add a stylesheet to be linked.
		* @param string $style Valid path to the stylesheet. Watch out for casing and whitespaces when using Document::getStylesheets();
		* @param string $media Media query this stylesheet should apply to.
		* @return void
		*/
		public function addStylesheet($style, $media = "all") {
			$this->stylesheets[$media][] = $style;
		}

		/**
		* Add a javascript file to be rendered.
		* @param string $script Valid path to the javascript file.
		* @param string $region Region of the DOM where this javascript should be rendered.
		* @return void
		*/
		public function addJavascript($script, $region = "footer") {
			$this->javascript[$region][] = $script;
		}

		/**
		* Get current stylesheets to be linked.
		* @param $media Only return stylesheets in this media query.
		* @return array
		*/
		public function getStylesheets($media = "all") {
			return isset($this->stylesheets[$media]) ? $this->stylesheets[$media] : [];
		}

		/**
		* Get current javascript files to be rendered.
		* @param string $region Only return javascript files belonging to this region.
		* @return array
		*/
		public function getJavascript($region = "footer") {
			return isset($this->javascript[$region]) ? $this->javascript[$region] : [];
		}
	}