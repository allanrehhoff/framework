<?php
	require __DIR__."/../src/startup.php";

	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	/**
	 * A simple controller class used for testing purposes only
	 */
	class MockController extends \Controller {
		public function index() {
			$this->children[] = new \Core\ClassName("Header"); 
		}

		private function privateFunction() {}

		protected function protectedFunction() {}
	}

	ini_set("display_errors", 1);