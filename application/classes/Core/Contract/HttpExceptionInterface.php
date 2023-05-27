<?php
namespace Core\Contract {
	interface HttpExceptionInterface {
		/**
		 * Return an integer representing a HTTP code
		 * @return int Any HTTP code
		 */
		public function getHttpCode() : int;
	}
}