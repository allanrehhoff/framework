<?php

namespace Core\ContentType;

/**
 * Enum representing different content types.
 */
enum ContentTypeEnum: string {
	case HTML = "html";
	case XML = "xml";
	case JSON = "json";

	/**
	 * Returns the class name associated with the content type.
	 *
	 * @return null|ContentTypeInterface Instance of the content type, null if unmatched
	 */
	public function getInstance(): null|ContentTypeInterface {
		return match ($this) {
			self::JSON => new Json(),
			self::XML => new Xml(),
			self::HTML => new Html()
		};
	}
}
