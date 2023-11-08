<?php
namespace Core\Output {
	
	/**
	 * Interface Acceptable
	 */
	interface ContentType {
		/**
		 * @param \Core\Template $iTemplate
		 */
		public function __construct(private \Core\Template $iTemplate);

		/**
		 * @param string $view
		 * @param array $data
		 */
		public function render(\Core\Response $iResponse);
	}
}