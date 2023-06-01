<?php
namespace Core {

	/**
	 * Encapsulates the details of a request and simplifies the process of working with incoming HTTP requests.
	 * It provides functionality for retrieving request parameters, headers, and simplifying file uploads.
	 */
	class Request {		
		/**
		 * @var array Contents of the $_GET super global
		 */
		public $get;
		
		/**
		 * @var array $post Contents of the $_POST super global
		 */
		public $post;

		/**
		 * @var array Contents of the $_SERVER super global
		 */
		public $server;

		/**
		 * @var array Contents of the $_FILES super global
		 */
		public $files;

		/**
		 * @var array Contents of the $_COOKIE super global
		 */
		public $cookie;

		/**
		 * @var array 
		 */
		private array $arguments;

		/**
		 * Sets initial state of super globals
		 */
		public function __construct() {
			$this->get = $_GET;
			$this->post = $_POST;
			$this->files = $_FILES;
			$this->cookie = $_COOKIE;
			$this->server = $_SERVER;

			if(empty($this->files) !== true) {
				$this->files = $this->reArrangeFilesArray($this->files);
			}

			if(IS_CLI === true) {
				$args = $this->server["argv"];
			} else {
				$args = trim($this->server["REQUEST_URI"], '/');
				$args = explode('/', $args);
				$args = array_filter($args);
			}

			$this->setArguments($args);
		}

		/**
		 * Set arguments parsed from path
		 * @param array $arguments
		 */
		public function setArguments(array $arguments) {
			$this->arguments = $arguments;
		}

		/**
		 * Get path that should be used by the router
		 * @return array
		 */
		public function getArguments() : array {
			return $this->arguments;
		}

		/**
		 * Re-arrange a $_FILES array to a more intuitive format
		 * @param array $files the $_FILES array
		 * @return array The re-arranged files array
		 */
		private function reArrangeFilesArray(array $files) {
			$result = [];
			$count  = count($files["name"]);
			$keys   = array_keys($files);
		
			for($i = 0; $i < $count; $i++) {
				foreach($keys as $key) {
					$result[$i][$key] = $files[$key][$i];
				}
			}
		
			return $result;
		}
	}
}