<?php
namespace Core {
	/**
	* Loads and setups the current configured theme in use.
	*
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/
	class Theme {
		/**
		* @var Holds the current theme name
		*/
		private $name;

		/**
		* Doesn't do much of interest, this shouldn't be required to mess with.
		* @return void
		*/
		public function __construct($themename) {
			$this->name = $themename;
			$this->theme = (new Configuration($this->getTemplatePath("/theme.json")));

			$this->addAssets();
		}

		/**
		* Get the path to the current active theme.
		* @return string
		*/
		public function getTemplatePath($tpl = '') {
			$path = \Registry::get("Core\Application")->getApplicationPath()."/application/themes/".$this->getName();

			if($tpl != '') {
				$path .= '/'.$tpl;
			}

			return $path;
		}

		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param (string) $url Path to element of which to create a URI.
		* @author Allan Thue Rehhoff
		* @return string
		*/
		public function getDirectoryUri($path = '/') {
			$protocol = SSL ? "https://" : "http://";
			$host  = $_SERVER['HTTP_HOST'];
			$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$baseurl = $protocol.$host.$path."/";

			return $baseurl.ltrim($path, '/');
		}

		/**
		* Get the current theme name loaded.
		* @return string
		*/
		public function getName() {
			return $this->name;
		}

		/**
		* Adds configured theme assets to the DOM\Document class
		* @uses \DOM\Document
		* @return void
		*/
		private function addAssets() {
			if(!empty($this->theme->get("javascript"))) {
				foreach($this->theme->get("javascript") as $javascript) {
					\DOM\Document::addJavascript($this->getDirectoryUri($javascript));
				}
			}

			if(!empty($this->theme->get("stylesheets"))) {
				foreach($this->theme->get("stylesheets") as $stylesheet) {
					\DOM\Document::addStylesheet($this->getDirectoryUri($stylesheet));
				}
			}
		}
	}
}