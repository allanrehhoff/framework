<?php
	ini_set("display_errors", 1);

	require __DIR__."/../src/Bootstrap/startup.php";

	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	/**
	 * Controllers
	 */
	class MockController extends \Controller {
		public function index(): void {
			$this->children[] = new \Core\ClassName("MockChild");

			$this->response->setView("mock");
		}

		public function withoutChildren(): void {
			$this->response->setView("without-children");
		}

		private function privateFunction(): void {}

		protected function protectedFunction(): void {}
	}

	class MockChildController extends \Controller {
		public static string $testkey = "test";

		public function index(): void {
			$this->response->data[self::$testkey] = "hello world";

			$this->response->setView("mockchild");
		}
	}

	class MockEventObject {
		public string $property = "";
	}

	class MockEventListener {
		public function handle(\MockEventObject $iMockEventObject): void {
			$iMockEventObject->property = __FUNCTION__;
		}

		public static function handleStatic(\MockEventObject $iMockEventObject): void {
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

		public static function withArguments(array $arguments): \Core\Request {
			$iRequest = self::new();
			$iRequest->setArguments($arguments);

			return $iRequest;
		}

		public static function withServerVars(array $arguments): \Core\Request {
			$arguments = array_change_key_case($arguments, CASE_UPPER);

			$iRequest = self::new();
			$iRequest->server = array_merge($iRequest->server, $arguments);

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

		public static function fromCleanState(): \Environment {
			return new \Environment;
		}
	}

