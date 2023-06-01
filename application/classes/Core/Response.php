<?php
namespace Core {
	/**
	 * Class Response
	 *
	 * Encapsulates the data necessary for rendering a view template.
	 */
	class Response {
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
		 * Get the data to be passed to the view template.
		 * @return array The view data.
		 */
		public function getData(): array {
			return $this->data;
		}

		/**
		 * @param string $view template name of the view
		 * @return void
		 */
		public function setView(string $view) : void {
			$this->view = $view;
		}

		/**
		 * @param string $view template name of the view
		 * @return void
		 */
		public function setData(array $data) : void {
			$this->data = $data;
		}

		/**
		 * Set a dynamic value for the title tag.
		 * 
		 * @param string $title a title to display in a template file.
		 * @return void
		 */
		final public function setTitle(string $title) : void {
			$this->data["title"] = sprintf(\Resource::getConfiguration()->get("titleFormat"), $title);
		}

		/**
		 * Get the current page title to be displayed.
		 * 
		 * @return string
		 */
		final public function getTitle() : string {
			return $this->data["title"];
		}

		/**
		 * Send HTTP code
		 * @param int $httpCode A http code to send
		 * @return void
		 */
		final public function sendHttpCode(int $httpCode) : void {
			http_response_code($httpCode);
		}
	}
}