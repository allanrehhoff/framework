<?php
namespace Core {
	/**
	 * Extended by \Core\ClassName and \Core\MethodName
	 */
	class MVCStructure {
		/**
		 * @var string $sanitizedString Holds the sanitized structure string, set by children
		 */
		protected string $sanitizedString = '';

		/**
		 * Returns the sanitized string
		 * @return string
		 */
		public function toString() : string {
			return $this->sanitizedString;
		}

		/**
		 * Returns the sanitized string without the "controller suffix"
		 * @return string
		 */
		public function toStringWithoutSuffix() : string {
			return preg_replace("/".\Controller::class."$/", '',  $this->toString());
		}
	}
}