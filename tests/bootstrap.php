<?php
	ini_set("display_errors", 1);

	require __DIR__."/../src/Bootstrap/startup.php";

	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	/**
	 * Controllers
	 */
	class MockController extends \Controller {
		public function index() {
			$this->children[] = new \Core\ClassName("MockChild");

			$this->response->setView("mock");
		}

		public function withoutChildren() {
			$this->response->setView("without-children");
		}

		private function privateFunction() {}

		protected function protectedFunction() {}
	}

	class MockChildController extends \Controller {
		public static string $testkey = "test";

		public function index() {
			$this->response->data[self::$testkey] = "hello world";

			$this->response->setView("mockchild");
		}
	}

	class MockEventObject {
		public string $property = "";
	}

	class MockEventListener {
		public function handle(\MockEventObject $iMockEventObject) {
			$iMockEventObject->property = __FUNCTION__;
		}

		public static function handleStatic(\MockEventObject $iMockEventObject) {
			$iMockEventObject->property = __FUNCTION__;
		}
	}

	/**
	 * Factories
	 */
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

	class EnvironmentFactory {
		public static function createFromString(string $string) : \Environment {
			$tmpfile = tempnam(sys_get_temp_dir(), "framework-env-test");
			file_put_contents($tmpfile, $string);

			return new \Environment($tmpfile);
		}

		public static function fromCleanState() {
			return new \Environment;
		}
	}

