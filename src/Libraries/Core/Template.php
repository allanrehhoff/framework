<?php
namespace Core {
	/**
	 * Loads and setups the current configured theme in use.
	 */
	final class Template {
		/**
		 * @var string Holds the current theme name
		 */
		private string $name;

		/**
		 * @var \Core\Assets The assets class
		 */
		public Assets $assets;

		/**
		 * @var \Configuration Holds the theme configuration object.
		 */
		private \Configuration $iConfiguration;

		/**
		 * Doesn't do much of interest, this shouldn't be required to mess with.
		 * @param \Core\Assets $iAssets Instance of assets to be rendered later on
		 * @return void
		 */
		public function __construct(\Core\Assets $iAssets) {
			$this->name = \Singleton::getConfiguration()->get("theme");
			$this->assets = $iAssets;

			$configurationFile = STORAGE . "/config/" . $this->name . ".theme.jsonc";
			$this->iConfiguration = (new \Configuration($configurationFile));

			if($this->iConfiguration->get("version.version") == "@version") {
				$this->iConfiguration->set("version.version", \Singleton::getConfiguration()->get("version"));
			}

			$this->registerAssets(
				$this->iConfiguration->get("assets")
			);
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
		 * @param string $shortname The shortened template name e.g partials/sidebar 
		 * @return string
		 */
		public function getPath(string $shortname = '') : string {
			$path = APP_PATH . "/Templates/" . $this->getName();

			if($shortname != '') {
				$path .= '/'.$shortname;
			}

			if(str_ends_with($path, ".tpl.php") !== true) {
				$path .= ".tpl.php";
			}

			return $path;
		}

		/**
		 * Get URI path to this theme
		 * @param string $url Path to element of which to create a URI.
		 * @return string
		 */
		public function getDirectoryUri(string $file = '/') : string {
			if(filter_var($file, FILTER_VALIDATE_URL) == $file) return $file;

			$result = sprintf(
				"%s/Templates/%s/%s",
				\Url::getBaseurl(),
				$this->getName(),
				ltrim($file, '/')
			);

			$result = $this->maybeAddVersionNumber($result);

			return $result;
		}

		/**
		 * Get the current theme name loaded.
		 * @return string
		 */
		public function getName() : string {
			return $this->name;
		}

		/**
		 * Maybe add version number to asset urls, only if the follow conditions are met
		 * 1. version.expose is set to true in application config
		 * 2. $url matches own baseurl
		 * @param string $url Url to return with version number
		 * @return string
		 */
		private function maybeAddVersionNumber(string $url) : string {
			if($this->iConfiguration->get("version.expose") === true) {
				$baseurl = \Url::getBaseurl();

				if(str_starts_with($url, $baseurl) === true) {
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
		private function registerAssets(\stdClass $files) : void {
			foreach($files->js as $region => $javascripts) {
				foreach($javascripts as $javascript) {
					$src = $this->getDirectoryUri($javascript);

					$this->assets->addJavascript($src);
				}
			}

			foreach($files->css as $region => $stylesheets) {
				foreach($stylesheets as $stylesheet) {
					$src = $this->getDirectoryUri($stylesheet);

					$this->assets->addStylesheet($src, $region);
				}
			}
		}
	}
}