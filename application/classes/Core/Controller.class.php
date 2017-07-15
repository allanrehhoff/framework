<?php
namespace Core {
	use Exception;

	class Controller {
		protected $application, $request;
		protected $data = [];

		public function __construct() {
			$this->application = Application::getInstance();
			$this->request = array_merge($_GET, $_POST);

			$this->database = new \Database\Connection(
				\Core\ConfigurationParser::getInstance()->get("database.host"),
				\Core\ConfigurationParser::getInstance()->get("database.username"),
				\Core\ConfigurationParser::getInstance()->get("database.password"),
				\Core\ConfigurationParser::getInstance()->get("database.name")
			);
		}

		public function getData() {
			return $this->data;
		}
	}
}