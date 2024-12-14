<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Request::class)]
class RequestTest extends TestCase {
	public function testGetArg() {
		$iRequest = \RequestFactory::withArguments(["cli"]);

		$this->assertEquals($iRequest->getArg(0), "cli");
	}

	public function testGetArgWithDefaults() {
		$iRequest = \RequestFactory::withArguments(["cli"]);

		$this->assertEquals($iRequest->getArg(1, ["cli", "default"]), "default");
	}

	public function testGetArgOutOfRange() {
		$iRequest = \RequestFactory::withArguments(["cli"]);

		$this->assertNull($iRequest->getArg(2, ["cli", "default"]));
	}

	public function testContentTypeIsJson() {
		$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "application/json"]);
		$iRequest->getConfiguration()->set("contentTypes.json.enable", true);

		$preferences = $iRequest->getContentTypePreferences();

		$this->assertEquals(["application/json" => 1], $preferences);
	}

	public function testContentTypeIsXml() {
		$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "application/xml"]);
		$iRequest->getConfiguration()->set("contentTypes.xml.enable", true);

		$preferences = $iRequest->getContentTypePreferences();

		$this->assertEquals(["application/xml" => 1], $preferences);
	}

	public function testContentTypeWithQuality() {
		$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8"]);

		$preferences = $iRequest->getContentTypePreferences();

		$this->assertEquals([
			'text/html' => 1,
			'application/xhtml+xml' => 1,
			'image/avif' => 1,
			'image/webp' => 1,
			'application/xml' => 0.9,
			'*/*' => 0.8,
		], $preferences);
	}

	public function testContentTypeWithQualityCanWeightHigher() {
		$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=2.9,image/avif,image/webp,*/*;q=0.8"]);
		$iRequest->getConfiguration()->set("contentTypes.xml.enable", true);

		$preferences = $iRequest->getContentTypePreferences();

		$this->assertEquals([
			'application/xml' => 2.9,
			'text/html' => 1,
			'application/xhtml+xml' => 1,
			'image/avif' => 1,
			'image/webp' => 1,
			'*/*' => 0.8,
		], $preferences);
	}
}
