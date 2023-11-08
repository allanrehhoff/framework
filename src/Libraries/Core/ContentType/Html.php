<?php
namespace Core\ContentType {

	/**
	 * Class Core\Output\Html
	 */
	final class Html implements ContentType {
		/**
		 * @return string
		 */
		public function getType(): string {
			return "text";
		}

		/**
		 * @return string
		 */
		public function getMedia(): string {
			return "html";
		}

		/**
		 * Render a view with data.
		 *
		 * @param string $file The path to the view file to be rendered.
		 * @param array $data An associative array of data to be made available to the view.
		 */
		public function stream(string $file, array $data) : void {
			extract($data, EXTR_SKIP);
			require $file;
		}
	}
}