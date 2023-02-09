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
		}

		/**
		 * Get path that should be used by the router
		 * @return string|array
		 */
		public function getPath() : string|array {
			return IS_CLI ? $this->server["argv"] : $this->server["REQUEST_URI"];
		}
	}
}