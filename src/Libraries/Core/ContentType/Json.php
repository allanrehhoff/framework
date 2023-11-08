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
		 * @param string $view Purposely ignored by this media type
		 * @param array $data An associative array of data to be encoded as json
		 */
		public function stream(string $view, array $data) : void {
			print json_encode($data);
		}
	}
}