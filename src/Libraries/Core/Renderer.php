<?php

namespace Core;

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
	private Response $iResponse;

	/**
	 * Renderer constructor.
	 *
	 * @param Template $iTemplate The template engine used for rendering views.
	 * @param Response $iResponse The response object containg data to render.
	 */
	public function __construct(Template $iTemplate, Response $iResponse) {
		$this->iTemplate = $iTemplate;
		$this->iResponse = $iResponse;
	}

	/**
	 * Get the response to be rendered
	 * @return Response
	 */
	public function getResponse(): Response {
		return $this->iResponse;
	}

	/**
	 * Get the template/theme object.
	 *
	 * @return Template The template engine.
	 */
	public function getTemplate(): Template {
		return $this->iTemplate;
	}

	/**
	 * Renders the given response.
	 *
	 * @return void
	 */
	public function render(): void {
		$view = $this->getResponse()->getView();
		$data = $this->getResponse()->getData();

		$file = $this->getTemplate()->getViewPath($view);

		$this->getResponse()->getContentType()->stream($data, $file);
	}
}
