<?php
namespace Core {
	class MVCStructure {
		/**
		 * @var string holds the sanitized structure string, set by children
		 */
		protected $sanitizedString = '';

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
			return preg_replace("/Controller$/", '',  $this->toString());
		}


		/**
		 * Also returns the sanitized controller class
		 * @return string
		 */
		//public function __toString() {
		//	return $this->toString();
		//}
	}
}