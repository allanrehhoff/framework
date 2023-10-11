<?php
	require __DIR__."/../src/startup.php";

	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	/**
	 * A simple controller class used for testing purposes only
	 */
	class MockController extends \Controller {
		public function index() {
			$this->children[] = new \Core\ClassName("MockChild"); 
		}

		private function privateFunction() {}

		protected function protectedFunction() {}
	}

	class MockChildController extends \Controller {
		public static string $testkey = "test";

		public function index() {
			$this->response->data[self::$testkey] = "hello world";
		}
	}

	ini_set("display_errors", 1);