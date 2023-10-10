<?php
namespace Core\HttpError {

	/**
	 * The base class for all HTTP exceptions to extend upon
	 * This class is intentionally abstract
	 * As it should not be instantiated directly
	 * and only serves as a catch-all class for
	 * all http error exceptions extending this 
	 */
	abstract class StatusCode extends \Exception {
		/**
		 * Return an integer representing a HTTP code
		 * @return int Any HTTP code
		 */
		abstract public function getHttpCode() : int;

		/**
		 * Returns a class shortname matching the name
		 * of the http error exception being thrown.
		 * The request should be internally redirected
		 * to a controller of this name responsible
		 * of handling the remainder of the request.
		 * @return string
		 */
		public function getClassName() : string {
			return substr(strrchr(static::class, '\\'), 1);
		}
	}
}