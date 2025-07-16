<?php

namespace Core\StatusCode;

/**
 * The base class for all HTTP exceptions to extend upon
 * This class is intentionally abstract
 * As it should not be instantiated directly
 * and only serves as a catch-all class for
 * all http error exceptions extending this 
 */
interface StatusCode {
	/**
	 * Return an integer representing a HTTP code
	 * @return int Any HTTP code
	 */
	public static function getHttpCode(): int;

	/**
	 * Returns a class name matching the name
	 * of the http error exception being thrown.
	 * @return \Core\ClassName
	 */
	public static function getClassName(): \Core\ClassName;
}
