<?php
namespace Core {
	use Exception;

	class Controller {
		protected $application, $request, $title;
		protected $data = [];

		public function __construct() {
			$this->application = \Registry::get("Core\Application");
			$this->request = array_merge($_GET, $_POST);

			$this->database = new \Database\Connection(
				\Registry::get("Core\ConfigurationParser")->get("database.host"),
				\Registry::get("Core\ConfigurationParser")->get("database.username"),
				\Registry::get("Core\ConfigurationParser")->get("database.password"),
				\Registry::get("Core\ConfigurationParser")->get("database.name")
			);
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
			$this->title = $title;
		}

		/**
		* Get the current page title to be displayed.
		* @return string
		*/
		public function getTitle() {
			if(trim($this->title) != '') {
				$title = sprintf($this->application->config->get("base_title"), $this->title);
			} else {
				$title = sprintf($this->application->config->get("base_title"), '');
			}

			return $title;
		}
	}
}