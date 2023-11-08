<?php
namespace Core\Output {

	/**
	 * Class Core\Output\Html
	 *
	 * This class is responsible for rendering views with data.
	 */
	final class Html implements ContentType {
		/**
		 * @param \Core\Template $iTemplate
		 */
		public function __construct(private \Core\Template $iTemplate) {}

		/**
		 * Render a view with data.
		 *
		 * @param string $view The path to the view file to be rendered.
		 * @param array $data An associative array of data to be made available to the view.
		 */
		public function render(\Core\Response $iResponse) {
			$view = $this->iTemplate->getPath($iResponse->getView());
			$data = $iResponse->getData();

			if($view == '') {
				throw new \Core\Exception\Governance("Cannot render an empty view, controller must use \$this->response->setView(); or exit should be called");
			}

			\Core\Event::trigger("core.output.html", $view, $data);

			extract($data, EXTR_SKIP);
			require $view;
		}
	}
}