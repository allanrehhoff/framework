<?php

namespace Core\Attributes;

use Attribute;
use Core\ContentType\ContentTypeEnum;

/**
 * Attribute for specifying allowed content types for controllers or methods.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AllowedContentTypes {
	private array $contentTypes;

	/**
	 * Constructor
	 *
	 * @param string ...$contentTypes
	 */
	public function __construct(string ...$contentTypes) {
		foreach ($contentTypes as $contentType) {
			$this->contentTypes[$contentType] = ContentTypeEnum::from($contentType);
		}
	}

	/**
	 * Get allowed content types.
	 * @return array
	 */
	public function getContentTypes(): array {
		return $this->contentTypes;
	}
}
