<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);

define('TESTS_RUNNING', true);

require __DIR__ . "/../src/Libraries/Bootstrap/Bootstrap.php";

(new \Bootstrap\Bootstrap)->startup();

\Registry::set(new \Configuration(STORAGE . "/config/global.jsonc"));

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

	#[\Core\Attributes\RespondWith('xml')]
	public function methodAllowsXml() {
	}

	#[\Core\Attributes\RespondWith('json')]
	public function methodAllowsJson() {
	}

	#[\Core\Attributes\RespondWith('json', 'xml')]
	public function methodAllowsJsonAndXml() {
	}

	private function privateFunction(): void {
	}

	protected function protectedFunction(): void {
	}
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
	public static function new(): \Core\Request {
		return new \Core\Request();
	}

	public static function withArguments(array $arguments): \Core\Request {
		$iRequest = self::new();
		$iRequest->setArguments($arguments);

		return $iRequest;
	}

	public static function withServerVars(array $arguments): \Core\Request {
		$arguments = array_change_key_case($arguments, CASE_UPPER);

		$server = $_SERVER;

		$_SERVER = array_merge($server, $arguments);

		$iRequest = self::new();

		$_SERVER = $server;

		return $iRequest;
	}
}

class ResponseFactory {
	public static function new(): \Core\Response {
		return new \Core\Response;
	}
}

class RouterFactory {
	public static function withRequestArguments(array $arguments): \Core\Router {
		$iRouter = new \Core\Router(
			\RequestFactory::withArguments($arguments),
			\ResponseFactory::new()
		);

		return $iRouter;
	}
}

class EnvironmentFactory {
	public static function createFromString(string $string): \Environment {
		$tmpfile = tempnam(sys_get_temp_dir(), "framework-env-test");
		file_put_contents($tmpfile, $string);

		return new \Environment($tmpfile);
	}

	public static function fromCleanState(): \Environment {
		return new \Environment;
	}
}

class TemplateFactory {
	public static function new(): \Core\Template {
		return new \Core\Template(new \Core\Assets);
	}
}

class RendererFactory {
	public static function html() {
		$iTemplate = TemplateFactory::new();
		return new \Core\Renderer($iTemplate, new \Core\ContentType\Html);
	}

	public static function json() {
		$iTemplate = TemplateFactory::new();
		return new \Core\Renderer($iTemplate, new \Core\ContentType\Json);
	}

	public static function xml() {
		$iTemplate = TemplateFactory::new();
		return new \Core\Renderer($iTemplate, new \Core\ContentType\Xml);
	}
}
