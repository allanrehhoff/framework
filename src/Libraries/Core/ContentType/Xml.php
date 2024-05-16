<?php

namespace Core\ContentType;

use \SimpleXMLElement;

/**
 * Class Core\ContentType\Json
 *
 * This class is responsible for rendering views with data.
 */
final class Xml implements ContentTypeInterface {
	/**
	 * @return string
	 */
	public function getType(): string {
		return "application";
	}

	/**
	 * @return string
	 */
	public function getMedia(): string {
		return "xml";
	}

	/**
	 * Converts an array to XML and returns a SimpleXMLElement.
	 *
	 * @param array $data The array to convert to XML.
	 * @param null|SimpleXMLElement $iSimpleXMLElement The XML element to append to (use null for the root element).
	 *
	 * @return SimpleXMLElement The XML representation of the array.
	 */
	public function arrayToXml(array $data, ?SimpleXMLElement $iSimpleXMLElement = null): SimpleXMLElement {
		if ($iSimpleXMLElement === null) {
			$iSimpleXMLElement = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data/>');
		}

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				// Handle multidimensional arrays recursively
				$child = $iSimpleXMLElement->addChild($key);
				$this->arrayToXml($value, $child);
			} else {
				// Handle flat key-value pairs
				$value = \Escape::string($value);
				$iSimpleXMLElement->addChild($key, $value);
			}
		}

		return $iSimpleXMLElement;
	}

	/**
	 * Render data as XML
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
	 * @param array $data An associative array of data to be encoded as json
	 * @param string $view Purposely ignored by this media type
	 * @return void
	 */
	public function stream(array $data, ?string $view = ''): void {
		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
		print $this->arrayToXml($data)->saveXML();
	}
}
