<?php
namespace Core {
	use Exception;

	/**
	* The core controller which subcontrollers should extend upon.
	* @author Allan Thue Rehhoff
	*/
	class Controller {
		protected $application, $view, $request, $title;
		protected $data = [];
		private $theme;

		/**
		* Contsructs the overall environment, setting up helpers and initial variables.
		* @uses \Registry
		* @uses \DOM\Document
		* @uses \Core\Theme
		* @return void
		*/
		public function __construct() {
			$this->request = array_merge($_GET, $_POST, $_COOKIE);
			$this->configuration = \Registry::get("Core\Configuration");
			$this->application = \Registry::get("Core\Application");
			$this->view = $this->application->arg(0);

			$this->database = new \Database\Connection(
				$this->configuration->get("database.host"),
				$this->configuration->get("database.username"),
				$this->configuration->get("database.password"),
				$this->configuration->get("database.name")
			);

			$this->document = \Registry::set(new \DOM\Document);
			$this->theme = \Registry::set(new Theme($this->configuration->get("theme")));

			$this->data["header"] = $this->getView("header");
			$this->data["footer"] = $this->getView("footer");

			$this->data["stylesheets"] = \DOM\Document::getStylesheets();
			$this->data["javascript"]  = \DOM\Document::getJavascript("footer");
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
		* @return void
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
		* @param (string) $tpl name of the template file to get path for,
		* @return string
		*/
		public function getView($tpl = null) {
			if($tpl === null) {
				$tpl = $this->view;
			}

			return $this->theme->getTemplatePath(basename($tpl).".tpl.php");
		}

		/**
		* Checks if the requested controller has a corresponding view.
		* @return bool
		*/
		public function hasView() {
			return $this->getView() !== null;
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