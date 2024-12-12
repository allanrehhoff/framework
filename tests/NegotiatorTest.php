<?php

use Core\ContentType\Negotiator;
use Core\ContentType\ContentTypeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Core\Request;
use Core\Router;

/**
 * Unit tests for the Negotiator class
 */
#[CoversClass(Negotiator::class)]
class NegotiatorTest extends TestCase {
	/** @var Router */
	private Router $router;

	/** @var Request */
	private Request $request;

	/** @var Negotiator */
	private Negotiator $negotiator;

	/**
	 * Test content type negotiation defaults to configuration fallback.
	 */
	public function testNegotiationDefaultsToConfiguration(): void {
		$iRouter = \RouterFactory::withRequestArguments([
			"controller" => "MockController",
			"method" => "index"
		]);

		$iRequest = \RequestFactory::withServerVars([
			"HTTP_ACCEPT" => "application/json"
		]);

		$iRequest->getConfiguration()->set("defaultType", "html");

		$contentType = (new Negotiator($iRouter, $iRequest))->getContentType();

		$this->assertInstanceOf(ContentTypeInterface::class, $contentType);
		$this->assertEquals($contentType::class, \Core\ContentType\Html::class);
	}

	/**
	 * Test content type negotiation prefers allowed types over configuration.
	 */
	public function testNegotiationPrefersTypeAllowedByConfig(): void {
		$iRouter = \RouterFactory::withRequestArguments(["MockController", "index"]);

		$iRequest = \RequestFactory::withServerVars([
			"HTTP_ACCEPT" => "application/json"
		]);

		$iRequest->getConfiguration()->set("defaultType", "html");
		$iRequest->getConfiguration()->set("contentTypes.json.enable", true);

		$contentType = (new Negotiator($iRouter, $iRequest))->getContentType();

		$this->assertInstanceOf(ContentTypeInterface::class, $contentType);
		$this->assertEquals($contentType::class, \Core\ContentType\Json::class);
	}

	/**
	 * Test content type negotiation prefers allowed types over configuration.
	 */
	public function testNegotiationPrefersTypeAllowedByMethodAttribute(): void {
		$iRouter = \RouterFactory::withRequestArguments([
			"mock-controller",
			"method-allows-json-and-xml"
		]);

		$iRequest = \RequestFactory::withServerVars([
			"HTTP_ACCEPT" => "application/json"
		]);

		$iRequest->getConfiguration()->set("defaultType", "html");
		$iRequest->getConfiguration()->set("contentTypes.json.enable", true);

		$contentType = (new Negotiator($iRouter, $iRequest))->getContentType();

		$this->assertInstanceOf(ContentTypeInterface::class, $contentType);
		$this->assertEquals($contentType::class, \Core\ContentType\Json::class);
	}

	/**
	 * Test negotiation rejects an unacceptable content type
	 */

	/**
	 * Test content type negotiation fallback.
	 */
	public function testNegotiationFallbacksToDefault(): void {
		$iRouter = \RouterFactory::withRequestArguments([
			"mock-controller",
			"method-allows-json"
		]);

		$iRequest = \RequestFactory::withServerVars([
			"HTTP_ACCEPT" => "application/xml"
		]);

		$iRequest->getConfiguration()->set("defaultType", "html");
		$iRequest->getConfiguration()->set("contentTypes.json.enable", true);

		$contentType = (new Negotiator($iRouter, $iRequest))->getContentType();

		$this->assertInstanceOf(ContentTypeInterface::class, $contentType);
		$this->assertEquals($contentType::class, \Core\ContentType\Html::class);
	}
}
