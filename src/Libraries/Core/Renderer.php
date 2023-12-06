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

			\Core\Event::trigger(
				$event,
				$view,
				$data
			);

			$this->iContentType->stream($data, $file);
		}
	}
}