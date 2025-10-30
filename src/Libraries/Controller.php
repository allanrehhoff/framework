<?php

/**
 * This is the controller which subcontrollers should extend upon.
 * Every controller must define their own 'index' method, which is
 * the default method invoked for all controllers.
 */

use \Core\Assets;
use \Core\Request;
use \Core\Response;
use \Core\Renderer;
use \Core\Template;
use \Core\ClassName;

abstract class Controller {

	/**
	 * @var null|\Controller Holds the parent controller instance
	 */
	protected null|\Controller $parent = null;

	/**
	 * @var Request Current request object
	 */
	protected Request $request;

	/**
	 * @var Response Current request object
	 */
	protected Response $response;

	/**
	 * @var Template Current template object
	 */
	protected Template $template;

	/**
	 * @var Assets
	 */
	protected Assets $assets;

	/**
	 * @var Renderer
	 */
	protected Renderer $renderer;

	/**
	 * @var array Child controllers classes to be executed when the main one finalizes.
	 */
	protected array $children = [];

	/**
	 * Controllers should declare this function instead of a constructor.
	 * @return void
	 */
	abstract protected function index(): void;

	/**
	 * Extending child controllers must not have a constructor.
	 * @param \Core\Request    $iRequest    The current request object
	 * @param \Core\Response   $iResponse   The current response object
	 * @param null|\Controller $iController The parent controller, if this controller is a child, null if it's the top-level controller
	 */
	public function __construct(Request $iRequest, Response $iResponse, null|Controller $iController = null) {
		$this->parent = $iController;
		$this->request = $iRequest;
		$this->response = $iResponse;

		$this->template = \Registry::get(Template::class);

		if ($iController === null && $iResponse->getContentType()->getMedia() === "html") {
			$this->children[] = new ClassName("Partial\Header");
			$this->children[] = new ClassName("Partial\Footer");

			$this->response->setTitle(\Arr::slice($this->request->getArguments(), -1)[0] ?? '');
		}
	}

	/**
	 * Notify of backwards compatibility breakages when setting data
	 * When accessing an array using the $this->data["key"] = "value" syntax,
	 * it actually goes through __get(); instead of __set();
	 * This is because it is the array stored in $this->data that is being modified rather than the class member
	 * @param string $name Property being accessed
	 * @return mixed
	 */
	public function __get(string $name): mixed {
		if ($name == "data") {
			throw new \RuntimeException("Setting data on controller object is not allowed, use '\$this->response->data[]' instead");
		}

		return $this->$name;
	}

	/**
	 * Set current controllers parent.
	 * 
	 * @param \Controller $iController Controller instance to use as parent.
	 * @return void
	 */
	final public function setParent(\Controller $iController): void {
		$this->parent = $iController;
	}

	/**
	 * Get current parent controller instance
	 * 
	 * @return null|\Controller The current parent controller instance, will be null for the root controller.
	 */
	final public function getParent(): null|\Controller {
		return $this->parent;
	}

	/**
	 * Get names of children controllers
	 * 
	 * @return array
	 */
	final public function getChildren(): array {
		return $this->children;
	}

	/**
	 * @return Template
	 */
	final public function getTemplate(): Template {
		return $this->template;
	}

	/**
	 * @return Request
	 */
	final public function getRequest(): Request {
		return $this->request;
	}

	/**
	 * @return Response
	 */
	final public function getResponse(): Response {
		return $this->response;
	}
}
