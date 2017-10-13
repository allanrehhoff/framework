<?php
namespace Core {
	use Exception;

	/**
	* The core controller which subcontrollers should extend upon.
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/
	class Controller {
		protected $application, $view, $request, $title;
		protected $data = [];
		private $theme;

		/**
		* Contsructs the overall environment, setting up helpers and initial variables.
		* @return void
		*/
		public function __construct() {
			$this->request = array_merge($_GET, $_POST);
			$this->configuration = \Registry::get("Core\Configuration");
			$this->application = \Registry::get("Core\Application");
			$this->document = \Registry::set(new \DOM\Document);
			$this->theme = (new Configuration($this->application->getThemepath()."/theme.json"));
			$this->view = $this->application->arg(0);

			$this->database = new \Database\Connection(
				$this->configuration->get("database.host"),
				$this->configuration->get("database.username"),
				$this->configuration->get("database.password"),
				$this->configuration->get("database.name")
			);

			$this->data["header"] = $this->getViewPath("header");
			$this->data["footer"] = $this->getViewPath("footer");

			$this->setTitle("Frontpage");
			$this->addThemeAssets();
		}

		/**
		* Adds configured theme assets to the DOM\Document class
		* @uses \DOM\Document
		* @return void
		*/
		private function addThemeAssets() {
			if($this->theme->get("javascript")) {
				foreach($this->theme->get("javascript") as $javascript) {
					\DOM\Document::addJavascript(\Functions::url($javascript));
				}
			}

			if($this->theme->get("stylesheets")) {
				foreach($this->theme->get("stylesheets") as $stylesheet) {
					\DOM\Document::addStylesheet(\Functions::url($stylesheet));
				}
			}
		}

		/**
		* Provides data set by extending controllers.
		* @return array
		*/
		public function getData() {
			return $this->data;
		}

		/**
		* Set a dynamic value for the title tag.
		* @param (string) $title a title to display in a template file.
		* @return self
		*/
		public function setTitle($title) {
			$this->data["title"] = sprintf($this->configuration->get("base_title"), $title);
		}

		/**
		* Get the current page title to be displayed.
		* @return string
		*/
		public function getTitle() {
			return $this->data["title"];
		}


		/**
		* Get the path to a template file, ommit .tpl.php extension
		* TODO: cut .tpl.php from the $tpl param, if provided. (Find out if I can use basename()'s second argument)
		* @param (string) $tpl name of the template file to get path for,
		* @return string
		*/
		public function getViewPath($tpl = null) {
			if($tpl === null) {
				$tpl = $this->view;
			}

			return $this->application->getThemePath().'/'.basename($tpl).".tpl.php";
		}

		/**
		* Checks if the requested controller has a corresponding view.
		* @return bool
		*/
		public function hasView() {
			return $this->getViewPath() !== null;
		}

		/**
		* Convenience wrapper, for setting/overriding a view within any controller
		* @param Name of the view to use, without .tpl.php extensions.
		* @return bool
		*/
		protected function setView($view) {
			$this->view = $view;
		}
	}
}