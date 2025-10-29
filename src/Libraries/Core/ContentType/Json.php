<?php

namespace Core\ContentType;

/**
 * Class Core\ContentType\Json
 *
 * This class is responsible for rendering views with data.
 */
final class Json implements ContentTypeInterface {
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
		return "json";
	}

	/**
	 * Render data as json
	 *
	 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter
	 * @param array $data An associative array of data to be encoded as json
	 * @param null|string $view Purposely ignored by this media type
	 * @return void
	 */
	public function stream(array $data, null|string $view = ''): void {
		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
		print json_encode($data, JSON_PRETTY_PRINT);
	}
}
