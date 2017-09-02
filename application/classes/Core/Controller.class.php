<?php
namespace Core {
	use Exception;

	class Controller {
		protected $application, $request, $title;
		protected $data = [];
		private $theme;

		public function __construct() {
			$this->application = \Registry::get("Core\Application");
			$this->request = array_merge($_GET, $_POST);
			$this->setTitle("Frontpage");

			$this->theme = (new Configuration($this->application->getThemepath()."/theme.json"));
			$this->addThemeAssets();

			$this->database = new \Database\Connection(
				\Registry::get("Core\Configuration")->get("database.host"),
				\Registry::get("Core\Configuration")->get("database.username"),
				\Registry::get("Core\Configuration")->get("database.password"),
				\Registry::get("Core\Configuration")->get("database.name")
			);
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
		* @return (array)
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
			$this->data["title"] = sprintf(\Registry::get("Core\Configuration")->get("base_title"), $title);
		}

		/**
		* Get the current page title to be displayed.
		* @return string
		*/
		public function getTitle() {
			return $this->data["title"];
		}
	}
}