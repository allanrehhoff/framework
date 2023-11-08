<?php
namespace Core\Output {

	/**
	 * Class Core\Output\Json
	 *
	 * This class is responsible for rendering views with data.
	 */
	final class Json implements ContentType {
		/**
		 * @param \Core\Template $iTemplate
		 */
		public function __construct(private \Core\Template $iTemplate) {}

		/**
		 * Render data as json
		 *
		 * @param string $view Purposely ignored by this media type
		 * @param array $data An associative array of data to be encoded as json
		 */
		public function render(\Core\Response $iResponse) {
			$data = $iResponse->getData();

			\Core\Event::trigger("core.output.json", $data);

			print json_encode($data);
		}
	}
}