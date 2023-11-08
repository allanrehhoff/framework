<?php
namespace Core {
	use \Core\ContentType\ContentType;
	use \Core\Response;
	use \Core\Template;

	class Renderer {
		private Template $iTemplate;
		private ContentType $iContentType;

		public function __construct(Template $iTemplate, ContentType $iContentType) {
			$this->iTemplate = $iTemplate;
			$this->iContentType = $iContentType;
		}

		public function render(Response $iResponse) {
			$view = $iResponse->getView();
			$data = $iResponse->getData();

			$event = sprintf("core.output.%s", $this->iContentType->getMedia());
			$file = $this->iTemplate->getPath($view);

			if($this->iContentType::class == Html::class && $view == '') {
				throw new \Core\Exception\Governance("Cannot render an empty view, controller must use \$this->response->setView(); or exit should be called");
			}

			\Core\Event::trigger(
				$event,
				$view,
				$data
			);

			$this->iContentType->stream($file, $data);
		}
	}
}