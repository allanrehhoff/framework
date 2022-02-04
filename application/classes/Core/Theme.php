<?php
namespace Core {
	use Resource;

	/**
	* Loads and setups the current configured theme in use.
	*
	* @author Allan Thue Rehhoff
	*/
	class Theme {
		/**
		* @var string Holds the current theme name
		*/
		private $name;

		/**
		* @var \Core\Configuration Holds the theme configuration object.
		*/
		private $iConfiguration;

		/**
		* Doesn't do much of interest, this shouldn't be required to mess with.
		* @return void
		*/
		public function __construct(string $themename) {
			$this->name = $themename;

			$configurationFile = STORAGE . "/config/" . $this->name . ".theme.jsonc";
			$this->iConfiguration = (new Configuration($configurationFile));

			if($this->iConfiguration->get("version.version") == "@version") {
				$this->iConfiguration->set("version.version", Resource::get("Core\Application")->getConfiguration()->get("version"));
			}

			$this->addAssets();
		}

		/**
		 * Returns the configuration object associated with the application
		 * @return \Core\Configuration - application-wide configuration
		 */
		public function getConfiguration() : Configuration {
			return $this->iConfiguration;
		}

		/**
		* Get the path to the current active theme.
		* @return string
		*/
		public function getTemplatePath(string $tpl = '') : string {
			$path = Resource::get("Core\Application")->getApplicationPath()."/themes/".$this->getName();

			if($tpl != '') {
				$path .= '/'.$tpl;
			}

			return $path;
		}

		/**
		* Removes any need for having a hardcoded basepath in some obscure place
		* "cough"wordpress"cough"
		* @param string $url Path to element of which to create a URI.
		* @return string
		*/
		public function getDirectoryUri(string $file = '/') : string {
			$protocol = SSL ? "https://" : "http://";
			$host  = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "127.0.0.1";
			$path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
			$baseurl = $protocol.$host.$path;

			return $baseurl."/themes/".$this->getName()."/".ltrim($file, '/');
		}

		/**
		* Get the current theme name loaded.
		* @return string
		*/
		public function getName() : string {
			return $this->name;
		}

		/**
		* Maybe add version number to asset urls
		* @param string $url Url to return with version number
		* @return string
		*/
		private function maybeAddVersionNumber(string $url) : string {
			if($this->iConfiguration->get("version.expose")) {
				$proto = SSL ? "https://" : "http://";
				$string = $proto . $_SERVER["HTTP_HOST"];

				if(mb_substr($url, 0, mb_strlen($string)) === $string) {
					$url .= "?v=" . $this->iConfiguration->get("version.version");
				}
			}

			return $url;
		}

		/**
		* Adds configured theme assets to the Assets class
		* If theme assets appears to be an url, they'll be used as-is,
		* otherwise files are linked absolutely to the theme.
		* @uses \Assets
		* @return void
		*/
		private function addAssets() : void {
			if(!empty($this->iConfiguration->get("javascript"))) {
				foreach($this->iConfiguration->get("javascript") as $javascript) {
					if(filter_var($javascript, FILTER_VALIDATE_URL)) {
						$src = $javascript;
					} else {
						// Do not add files that does not exist in theme
						if(file_exists($this->getTemplatePath($javascript)) === false) continue;

						$src = $this->getDirectoryUri($javascript);
					}

					$src = $this->maybeAddVersionNumber($src);

					Resource::get("Assets")->addJavascript($src);
				}
			}

			if(!empty($this->iConfiguration->get("stylesheets"))) {
				foreach($this->iConfiguration->get("stylesheets") as $stylesheet) {
					if(filter_var($stylesheet, FILTER_VALIDATE_URL)) {
						$src = $stylesheet;
					} else {
						// Do not add files that does not exist in theme
						if(file_exists($this->getTemplatePath($stylesheet)) === false) continue;

						$src = $this->getDirectoryUri($stylesheet);
					}

					$src = $this->maybeAddVersionNumber($src);

					Resource::get("Assets")->addStylesheet($src);
				}
			}
		}
	}
}