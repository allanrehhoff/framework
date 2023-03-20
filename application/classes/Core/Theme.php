<?php
namespace Core {
	/**
	 * Loads and setups the current configured theme in use.
	 */
	class Theme {
		/**
		 * @var string Holds the current theme name
		 */
		private $name;

		/**
		 * @var \Core\Assets The assets class
		 */
		private $assets;

		/**
		 * @var \Configuration Holds the theme configuration object.
		 */
		private $iConfiguration;

		/**
		 * Doesn't do much of interest, this shouldn't be required to mess with.
		 * @param \Core\Assets $iAssets Instance of assets to be rendered later on
		 * @return void
		 */
		public function __construct(\Core\Assets $iAssets) {
			$this->name = \Resource::getConfiguration()->get("theme");
			$this->assets = $iAssets;

			$configurationFile = STORAGE . "/config/" . $this->name . ".theme.jsonc";
			$this->iConfiguration = (new \Configuration($configurationFile));

			if($this->iConfiguration->get("version.version") == "@version") {
				$this->iConfiguration->set("version.version", \Resource::getConfiguration()->get("version"));
			}

			$this->addAssets();
		}

		/**
		 * Get the \Core\Assets instance
		 * @return \Core\Assets
		 */
		public function getAssets() : \Core\Assets {
			return $this->assets;
		}

		/**
		 * Returns the configuration object associated with the theme
		 * @return \Configuration - application-wide theme configuration
		 */
		public function getConfiguration() : \Configuration {
			return $this->iConfiguration;
		}

		/**
		 * Get the path to the current active theme.
		 * @return string
		 */
		public function getTemplatePath(string $tpl = '') : string {
			$path = APP_PATH."/themes/".$this->getName();

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
			$protocol = IS_SSL ? "https://" : "http://";
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
			if($this->iConfiguration->get("version.expose") === true) {
				$proto = IS_SSL ? "https://" : "http://";
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
			if(!empty($this->iConfiguration->get("assets.js"))) {
				foreach($this->iConfiguration->get("assets.js") as $region => $javascripts) {
					foreach($javascripts as $javascript) {
						if(filter_var($javascript, FILTER_VALIDATE_URL)) {
							$src = $javascript;
						} else {
							// Do not add files that does not exist in theme
							if(file_exists($this->getTemplatePath($javascript)) === false) continue;

							$src = $this->getDirectoryUri($javascript);
						}

						$src = $this->maybeAddVersionNumber($src);

						$this->assets->addJavascript($src);
					}
				}
			}

			if(!empty($this->iConfiguration->get("assets.css"))) {
				foreach($this->iConfiguration->get("assets.css") as $region => $stylesheets) {
					foreach($stylesheets as $stylesheet) {
						if(filter_var($stylesheet, FILTER_VALIDATE_URL)) {
							$src = $stylesheet;
						} else {
							// Do not add files that does not exist in theme
							if(file_exists($this->getTemplatePath($stylesheet)) === false) continue;

							$src = $this->getDirectoryUri($stylesheet);
						}

						$src = $this->maybeAddVersionNumber($src);

						$this->assets->addStylesheet($src, $region);
					}
				}
			}
		}
	}
}