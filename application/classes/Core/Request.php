<?php
namespace Core {
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
		 * Sets initial state of super globals
		 */
		public function __construct() {
			$this->get = $_GET;
			$this->post = $_POST;
			$this->server = $_SERVER;
			$this->files = $_FILES;
			$this->cookie = $_COOKIE;

			if(empty($this->files) !== true) {
				$this->files = $this->reArrangeFilesArray($this->files);
			}
		}

		/**
		 * Get path that should be used by the router
		 * @return string|array
		 */
		public function getPath() : array {
			return IS_CLI ? $this->server["argv"] : $this->server["REQUEST_URI"];
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