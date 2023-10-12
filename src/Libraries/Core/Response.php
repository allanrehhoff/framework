<?php
namespace Core {
	/**
	 * Class Response
	 *
	 * Encapsulates the data necessary for rendering a view template.
	 */
	final class Response {
		/**
		 * @var string The name or path of the view template.
		 */
		private $view = '';

		/**
		 * @var array The data to be passed to the view template.
		 */
		public $data = [];

		/**
		 * Response constructor.
		 */
		public function __construct() {}

		/**
		 * Get the name or path of the view template.
		 * @return string The view template.
		 */
		public function getView(): string {
			return $this->view;
		}
		
		/**
		 * @param string $view template name of the view
		 * @return void
		 */
		public function setView(string $view) : void {
			$this->view = $view;
		}

		/**
		 * Get the data to be passed to the view template.
		 * @return array The view data.
		 */
		public function getData(): array {
			return $this->data;
		}

		/**
		 * @param array $data template name of the view
		 * @return void
		 */
		public function setData(array $data) : void {
			$this->data = $data;
		}

		/**
		 * Get the current page title to be displayed.
		 * 
		 * @return string
		 */
		public function getTitle() : string {
			return $this->data["title"];
		}

		/**
		 * Set a dynamic value for the title tag.
		 * 
		 * @param string $title a title to display in a template file.
		 * @return void
		 */
		public function setTitle(string $title) : void {
			$this->data["title"] = sprintf(
				\Singleton::getConfiguration()->get("titleFormat"),
				\HtmlEscape::escape($title)
			);
		}

		/**
		 * Send default HTTP headers
		 * @return void
		 */
		public function sendHttpHeaders() : void {
			// Cache headers
			header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}

		/**
		 * Send HTTP code
		 * @param int $httpCode A http code to send
		 * @return void
		 */
		public function sendHttpCode(int $httpCode) : void {
			http_response_code($httpCode);
		}
	}
}