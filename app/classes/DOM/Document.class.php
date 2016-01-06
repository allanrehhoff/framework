<?php
namespace DOM {	
	/**
	* Central class for setting javascripts and stylesheets to be rendered.
	*/
	class Document {
		private $stylesheets = [];
		private $javascript = [];
		protected $title = '';

		public function __construct() {
			$this->stylesheets["all"] = [];
			$this->javascript["footer"] = [];
		}

		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @return string
		*/
		public function url($path = '') {
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
		public function addStylesheet($style, $media = "all") {
			$this->stylesheets[$media][] = $style;
		}

		/**
		* Add a javascript file to be rendered.
		* @param (string) $script Valid path to the javascript file.
		* @param (string) $region Region of the DOM where this javascript should be rendered.
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
			return $this->stylesheets[$media];
		}

		/**
		* Get current javascript files to be rendered.
		* @param (string) $region Only return javascript files belonging to this region.
		* @return array
		*/
		public function getJavascript($region = "footer") {
			return $this->javascript[$region];
		}

		/**
		* Set a dynamic value for the title tag.
		* @param (string) $title a title to display in a template file.
		* @return self
		*/
		public function setTitle($title) {
			$this->title = $title;
			return $this;
		}

		/**
		* Get the current page title to be displayed.
		* @return string
		*/
		public function getTitle() {
			if(trim($this->title) != '') {
				$title = sprintf(\Registry::get("config")->get("base_title"), $this->title);
			} else {
				$title = sprintf(\Registry::get("config")->get("base_title"), str_replace("- ", '', \Registry::get("config")->get("base_title")));
			}
			return $title;
		}
	}
}