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
		 * @param string $view
		 * @param array $data
		 */
		public function stream(string $view, array $data) : void;
	}
}