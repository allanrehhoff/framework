<?php
namespace Core {

	/**
	* Loads and setups the current configured theme in use.
	*
	* @author Allan Thue Rehhoff
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
		public function __construct(string $themename) {
			$this->name = $themename;
			$this->theme = (new Configuration($this->getTemplatePath("/theme.json")));

			$this->addAssets();
		}

		/**
		* Get the path to the current active theme.
		* @return string
		*/
		public function getTemplatePath(string $tpl = '') : string {
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
		public function getDirectoryUri(string $file = '/') : string {
			$protocol = SSL ? "https://" : "http://";
			$host  = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "127.0.0.1";
			$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
			$baseurl = $protocol.$host.$path;

			return $baseurl."/application/themes/".$this->getName()."/".ltrim($file, '/');
		}

		/**
		* Get the current theme name loaded.
		* @return string
		*/
		public function getName() : string {
			return $this->name;
		}

		/**
		* Adds configured theme assets to the Document class
		* If theme assets appears to be an url, they'll be used as-is,
		* otherwise files are linked absolutely to the theme.
		* @uses \Document
		* @return void
		*/
		private function addAssets() : void {
			if(!empty($this->theme->get("javascript"))) {
				foreach($this->theme->get("javascript") as $javascript) {
					if(filter_var($javascript, FILTER_VALIDATE_URL)) {
						$src = $javascript;
					} else {
						// Do not add files that does not exist in theme
						if(file_exists($this->getTemplatePath($javascript)) === false) continue;

						$src = $this->getDirectoryUri($javascript);
					}

					\Registry::get("Document")->addJavascript($src);
				}
			}

			if(!empty($this->theme->get("stylesheets"))) {
				foreach($this->theme->get("stylesheets") as $stylesheet) {
					if(filter_var($stylesheet, FILTER_VALIDATE_URL)) {
						$src = $stylesheet;
					} else {
						// Do not add files that does not exist in theme
						if(file_exists($this->getTemplatePath($stylesheet)) === false) continue;

						$src = $this->getDirectoryUri($stylesheet);
					}

					\Registry::get("Document")->addStylesheet($src);
				}
			}
		}
	}
}