<?php
	class RequestTest extends PHPUnit\Framework\TestCase {
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

			$iContentType = $iRequest->getContentType();

			$this->assertEquals(\Core\ContentType\Json::class, $iContentType::class);
		}

		public function testContentTypeIsXml() {
			$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "application/xml"]);

			$iContentType = $iRequest->getContentType();

			$this->assertEquals(\Core\ContentType\Xml::class, $iContentType::class);
		}

		public function testContentTypeFallbackToHtml() {
			$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "image/avif,image/webp"]);

			$iContentType = $iRequest->getContentType();

			$this->assertEquals(\Core\ContentType\Html::class, $iContentType::class);
		}

		public function testContentTypeWithQuality() {
			$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8"]);

			$iContentType = $iRequest->getContentType();

			$this->assertEquals(\Core\ContentType\Html::class, $iContentType::class);
		}

		public function testContentTypeWithQualityCanWeightHigher() {
			$iRequest = \RequestFactory::withServerVars(["HTTP_ACCEPT" => "text/html,application/xhtml+xml,application/xml;q=2.9,image/avif,image/webp,*/*;q=0.8"]);

			$iContentType = $iRequest->getContentType();

			$this->assertEquals(\Core\ContentType\Xml::class, $iContentType::class);
		}
	}