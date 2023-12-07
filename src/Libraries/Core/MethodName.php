<?php
namespace Core {

	/**
	 * Sanitizes a string to a valid callable method name
	 */
	final class MethodName extends MVCStructure {
		/**
		 * @var string Default method name to be used, if a given method doesn't exist on a class.
		 */
		const DEFAULT = "index";

		/**
		 * @param string $string Takes a single argument as a string, this will be the method name to use.
		 * @throws \RuntimeException If $string cannot be matched by preg.
		 * @return void
		 */
		public function __construct(string $string) {
			preg_match_all("/\w+/", $string, $temp);

			if(($lastError = preg_last_error()) !== PREG_NO_ERROR) {
				throw new \RuntimeException(
					preg_last_error_msg(),
					$lastError
				);
			}

			foreach($temp[0] as $key => $word) {
				$temp[$key] = ucfirst(strtolower($word)); 
			}

			$this->sanitizedString = lcfirst(implode('', $temp));
		}
	}
}