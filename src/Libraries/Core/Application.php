<?php

namespace Core;

use \Core\ContentType\Negotiator;

/**
 * The main class for this application.
 */
final class Application {
	/**
	 * @var Router The router responsible for parsing request uri to callable system path
	 */
	private Router $router;

	/**
	 * @var Template The template object, holds information about templates/views
	 */
	private Template $template;

	/**
	 * Parse the current route and set caching as needed.
	 * 
	 * @param \Core\Router $iRouter Application arguments, usually url-parts divided by /, or argv.
	 */
	public function __construct(Router $iRouter) {
		$iNegotiator = new Negotiator($iRouter);
		$iTemplate = \Registry::set(new Template);

		$this->template = $iTemplate;
		$this->router = $iRouter;
		$this->router->getResponse()->setContentType($iNegotiator->getContentType());
	}

	/**
	 * Get the router being used
	 * 
	 * @return \Core\Router
	 */
	public function getRouter(): Router {
		return $this->router;
	}

	/**
	 * Get the template object
	 * @return \Core\Template
	 */
	public function getTemplate(): Template {
		return $this->template;
	}

	/**
	 * Dispatches a controller, based upon the requested path.
	 * Serves a NotFoundController if it doesn't exist
	 * 
	 * @return void
	 */
	public function run(): void {
		$route = $this->router->getRoute();

		$iController = $this->router->dispatch(...$route);

		$iResponse = $iController->getResponse();

		// Exiting here during tests prevents output capture
		// So we just return instead, to let tests conitnue
		if (TESTS_RUNNING) return;

		// Stopping here prevents further output when
		// in CLI mode, but exiting with the last
		// error code, if any.
		if (IS_CLI) exit(error_get_last()["type"] ?? 0);

		// The event that is about to be triggered
		$event = sprintf("core.output.%s", $iResponse->getContentType()->getMedia());

		/**
		 * Trigger an event before rendering the view.
		 *
		 * @param string $event The event name.
		 * @param Response $response The response object to be modified by listeners.
		 */
		\Core\Event::trigger($event, $iResponse);

		$this->send($iResponse);
	}

	/**
	 * Send the response to the client.
	 * 
	 * @param \Core\Response $iResponse The response object to be sent.
	 * @return void
	 */
	public function send(\Core\Response $iResponse): void {
		// Set the correct Content-Type header
		// At this point, the Content-Type will
		// have been negotiated and set appropriately
		$iResponse->addHeader(sprintf(
			"Content-Type: %s/%s; charset=utf-8",
			$iResponse->getContentType()->getType(),
			$iResponse->getContentType()->getMedia()
		));

		$iResponse->sendHeaders();
		$iTemplate = $this->getTemplate();

		(new Renderer($iTemplate, $iResponse))->render();
	}
}
