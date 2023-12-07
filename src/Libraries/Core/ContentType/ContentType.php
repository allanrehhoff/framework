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
		public function getType(): string;

		/**
		 * Get the content type short name
		 * @return string
		 */
		public function getMedia(): string;

		/**
		 * Send the resulting output of a rendered view with data
		 * @param array $data Data to expose within in the view
		 * @param string $view Path to a view file for relevant content types.
		 * @return void
		 */
		public function stream(array $data, string $view): void;
	}
}