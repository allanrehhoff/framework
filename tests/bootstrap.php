<?php
	require __DIR__."/../src/startup.php";

	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

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

	class RequestFactory {
		public static function new() : \Core\Request {
			return new \Core\Request();
		}

		public static function withArguments(array $arguments) {
			$iRequest = self::new();
			$iRequest->setArguments($arguments);

			return $iRequest;
		}
	}

	class ResponseFactory {
		public static function new() : \Core\Response {
			return new \Core\Response;
		}
	}

	class RouterFactory {
		public static function withRequestArguments(array $arguments) : \Core\Router {		
			$iRouter = new \Core\Router(
				\RequestFactory::withArguments($arguments),
				\ResponseFactory::new()
			);

			return $iRouter;
		}
	}

	ini_set("display_errors", 1);