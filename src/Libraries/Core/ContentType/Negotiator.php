<?php

namespace Core\ContentType;

use Core\ContentType\ContentTypeInterface;
use Core\ContentType\ContentTypeEnum;
use Core\Attributes\AllowedContentTypes;
use ReflectionClass;
use ReflectionMethod;

/**
 * Handles the negotiation of content types between request headers,
 * application configuration, controller/class and method attributes.
 */
class Negotiator {
	/**
	 * @var \Core\Request
	 */
	private \Core\Request $request;

	/**
	 * @var \Core\Router
	 */
	private \Core\Router $router;

	/**
	 * @param \Core\Router $iRouter
	 * @param \Core\Request $iRequest
	 */
	public function __construct(\Core\Router $iRouter, \Core\Request $iRequest) {
		$this->request = $iRequest;
		$this->router = $iRouter;
	}

	/**
	 * Determine the appropriate content type for the current request.
	 * @param string $controller Fully qualified controller class name
	 * @param string $method Controller method being invoked
	 * @return ContentTypeInterface
	 */
	public function getContentType(): ContentTypeInterface {
		// Use the request's content type (from Accept header) as a base
		$requestedContentTypes = $this->request->getContentTypePreferences();

		// Fetch allowed content types from configuration and attributes
		$availableContentTypes = $this->getAllowedContentTypes(...$this->router->getRoute());

		// Attempt to retrieve the default content type from configuration
		$iContentTypeEnum = ContentTypeEnum::tryFrom($this->request->getConfiguration()->get("defaultType"));

		foreach ($requestedContentTypes as $mimeType => $priority) {
			[$namespace, $contentType] = \Str::cut('/', $mimeType);

			if ($contentType === '*') {
				$iContentTypeEnum = current($availableContentTypes);
				break;
			}

			if (($availableContentTypes[$contentType] ?? null) !== null) {
				$iContentTypeEnum = $availableContentTypes[$contentType];
				break;
			}
		}

		if ($iContentTypeEnum === null) {
			throw new \Core\StatusCode\NotAcceptable;
		}

		return $iContentTypeEnum->getInstance();
	}

	/**
	 * Get allowed content types based on controller/method attributes and configuration.
	 * Setting attributes overrides configuration.
	 * @param \Core\ClassName $controller Fully qualified controller class name
	 * @param \Core\MethodName $method Controller method being invoked
	 * @return array List of allowed content types
	 */
	private function getAllowedContentTypes(\Core\ClassName $iClassName, \Core\MethodName $iMethodName): array {
		$allowedContentTypes = [];

		// Reflect controller and method attributes
		$iReflectionClass = new ReflectionClass($iClassName->toString());
		$iReflectionMethod = new ReflectionMethod($iClassName->toString(), $iMethodName->toString());

		foreach ([$iReflectionClass, $iReflectionMethod] as $reflector) {
			$attributes = $reflector->getAttributes(AllowedContentTypes::class);
			foreach ($attributes as $iReflectionAttribute) {
				/** @var AllowedContentTypes $iAllowedContentTypes */
				$iAllowedContentTypes = $iReflectionAttribute->newInstance();
				$allowedContentTypes = array_merge($allowedContentTypes, $iAllowedContentTypes->getContentTypes());
			}
		}

		if (empty($allowedContentTypes)) {
			$configContentTypes = $this->request->getConfiguration()->get('contentTypes');

			foreach ($configContentTypes as $contentType => $config) {
				if ($config->enable) {
					$allowedContentTypes[$contentType] = ContentTypeEnum::from($contentType);
				}
			}
		}

		return $allowedContentTypes;
	}
}
