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
		private string $view = '';

		/**
		 * @var array The data to be passed to the view template.
		 */
		public array $data = [];

		/**
		 * @var array Headers to be sent when sendHttpHeaders are called
		 * 			  Default headers includes no-cache headers.
		 */
		private array $headers = [
			["Cache-Control: no-store, no-cache, must-revalidate, max-age=0"],
			["Cache-Control: post-check=0, pre-check=0", false],
			["Pragma: no-cache"]
		];

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
				\Escape::string($title)
			);
		}

		/**
		 * Queue header to be sent
		 * @param string $header A fully constructed header to sent.
		 * @param bool $replace [optional] The optional replace parameter indicates
		 * 						whether the header should replace a previous similar header,
		 * 						or add a second header of the same type. By default it will replace,
		 * 						but if you pass in false as the second argument you can force multiple headers of the same type.
		 */
		public function addHeader(string $header, bool $replace = true) {
			$this->headers[] = [$header, $replace];
		}

		/**
		 * Send HTTP code
		 * @param int $httpCode A http code to send
		 * @return void
		 */
		public function sendHttpCode(int $httpCode) : void {
			http_response_code($httpCode);
		}

		/**
		 * Send default HTTP headers
		 * @return void
		 */
		public function sendHeaders() : void {
			foreach($this->headers as $header) {
				header(...$header);
			}
		}
	}
}