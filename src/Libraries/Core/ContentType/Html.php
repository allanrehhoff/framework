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
		 * @param array $data An associative array of data to be made available to the view.
		 * @param string $view The path to the view file to be rendered.
		 */
		public function stream(array $data, string $view) : void {
			if($view == '') {
				throw new \Core\Exception\Governance("Cannot render empty view, \$this->response->setView(); or exit should be called");
			}
		
			extract($data, EXTR_SKIP);
			require $file;
		}
	}
}