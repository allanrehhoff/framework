<?php

namespace Core\Attributes;

use Attribute;
use Core\ContentType\ContentTypeEnum;

/**
 * Attribute for specifying allowed content types for controllers or methods.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RespondWith {
	private array $contentTypes;

	/**
	 * Constructor
	 *
	 * @param string ...$contentTypes List of allowed content types.
	 *                                 Each content type should be a valid value from ContentTypeEnum.
	 *                                 Example: 'json', 'xml' or 'html'.
	 *                                 If no content types are provided, all configured content types are allowed.
	 *                                 If an invalid content type is provided, it will throw an exception.
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
