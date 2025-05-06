<?php

namespace Core;

use \Core\ContentType\ContentTypeInterface;
use \Core\Response;
use \Core\Template;

/**
 * Class Renderer
 *
 * The Renderer class is responsible for rendering views based on the given Template, in other words: Theme, and ContentType.
 */
class Renderer {
	/**
	 * @var Template $iTemplate The template engine used for rendering views.
	 */
	private Template $iTemplate;

	/**
	 * @var ContentTypeInterface $iContentType The content type used for rendering output.
	 */
	private ContentTypeInterface $iContentType;

	/**
	 * Renderer constructor.
	 *
	 * @param Template $iTemplate The template engine used for rendering views.
	 * @param ContentTypeInterface $iContentType The content type used for rendering output.
	 */
	public function __construct(Template $iTemplate, ContentTypeInterface $iContentType) {
		$this->iTemplate = $iTemplate;
		$this->iContentType = $iContentType;
	}

	/**
	 * Renders the given response.
	 *
	 * @param Response $iResponse The response to be rendered.
	 * @return void
	 */
	public function render(Response $iResponse): void {
		$view = $iResponse->getView();
		$data = $iResponse->getData();

		$event = sprintf("core.output.%s", $this->iContentType->getMedia());
		$file = $this->iTemplate->getViewPath($view);

		/**
		 * Trigger an event before rendering the view.
		 *
		 * @param string $event The event name.
		 * @param string $view The view to be rendered.
		 * @param array $data The data to be passed to the view.
		 */
		\Core\Event::trigger(
			$event,
			$view,
			$data
		);

		$this->iContentType->stream($data, $file);
	}
}
