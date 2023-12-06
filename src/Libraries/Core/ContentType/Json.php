<?php
namespace Core\ContentType {

	/**
	 * Class Core\ContentType\Json
	 *
	 * This class is responsible for rendering views with data.
	 */
	final class Json implements ContentType {
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
		 * @param string $view Purposely ignored by this media type
		 */
		public function stream(array $data, string $view = '') : void {
			// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter
			print json_encode($data);
		}
	}
}