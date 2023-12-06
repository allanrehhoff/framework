<?php
namespace Core\ContentType {
	
	/**
	 * Interface ContentType
	 */
	interface ContentType {

		/**
		 * Get the content type short name
		 * @return string
		 */
		public function getType() : string;

		/**
		 * Get the content type short name
		 * @return string
		 */
		public function getMedia() : string;

		/**
		 * Send the resulting output of a rendered view with data
		 * @param array $data
		 * @param string $view
		 */
		public function stream(array $data, string $view) : void;
	}
}