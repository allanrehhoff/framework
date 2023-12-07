<?php
	/**
	* This is the controller which subcontrollers should extend upon.
	* Every controller must define their own 'index' method, which is
	* the default method invoked for all controllers.
	*/
	abstract class Controller {

		/**
		 * @var ?\Controller Holds the parent controller instance
		 */
		protected ?\Controller $parent = null;

		/**
		 * @var \Core\Request Current request object
		 */
		protected \Core\Request $request;

		/**
		 * @var \Core\Response Current request object
		 */
		protected \Core\Response $response;

		/**
		 * @var \Core\Application The application object
		 */
		protected \Core\Application $application;

		/**
		 * @var \Core\Router Current router object in use
		 */
		protected \Core\Router $router;

		/**
		 * @var \Core\Template Current template object
		 */
		protected \Core\Template $template;

		/**
		 * @var \Core\Assets
		 */
		protected \Core\Assets $assets;

		/**
		 * @var \Core\ContentType\ContentType
		 */
		protected \Core\ContentType\ContentType $contentType;

		/**
		 * @var \Core\Renderer
		 */
		protected \Core\Renderer $renderer;

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
		 * @param \Core\Application $iApplication The application class invoking this controller.
		 * @param null|\Controller $iController The parent controller, if this controller is a child, null if it's the top-level controller
		 */
		public function __construct(\Core\Application $iApplication, ?\Controller $iController = null) {
			$this->parent = $iController;
			$this->application = $iApplication;

			$this->router = $this->application->getRouter();
			$this->request = $this->router->getRequest();
			$this->response = $this->router->getResponse();

			// While it's safe to load these children class in cli
			// there's as of yet, no beneficial reason to.
			// So we'll save the memory and CPU cycles
			if(IS_CLI === false) {
				$this->contentType = $this->request->getContentType();

				$this->template = new \Core\Template(new \Core\Assets);

				$this->renderer = new \Core\Renderer(
					$this->template,
					$this->contentType
				);

				$this->response->addHeader(sprintf(
					"Content-Type: %s/%s; charset=utf-8",
					$this->contentType->getType(),
					$this->contentType->getMedia()
				));

				if($iController === null && $this->contentType::class == \Core\ContentType\Html::class) {
					$this->children[] = new \Core\ClassName("Header");
					$this->children[] = new \Core\ClassName("Footer");

					$this->response->setTitle(array_slice($this->request->getArguments(), -1)[0] ?? '');
				}
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
			if($name == "data") {
				throw new \RuntimeException("Setting data on controller object is not allowed, use '\$this->response->data[]' instead");
			}

			return $this->$name;
		}

		/**
		 * Send output to client based on their preffered media type
		 * @return void
		 */
		final public function output(): void {
			// Stopping here, will prevent errors such as
			// "Call to a member function getTemplatePath() on null"
			// This is fine as we don't need a view layer for CLI
			if(IS_CLI) exit(0);

			$this->response->sendHeaders();
			
			$this->renderer->render($this->response);
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
		final public function getParent(): ?\Controller {
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
		 * @return \Core\Template
		 */
		final public function getTemplate(): \Core\Template {
			return $this->template;
		}

		/**
		 * @return \Core\Application
		 */
		final public function getApplication(): \Core\Application {
			return $this->application;
		}

		/**
		 * @return \Core\Router
		 */
		final public function getRouter(): \Core\Router {
			return $this->router;
		}

		/**
		 * @return \Core\Request
		 */
		final public function getRequest(): \Core\Request {
			return $this->request;
		}

		/**
		 * @return \Core\Response
		 */
		final public function getResponse(): \Core\Response {
			return $this->response;
		}
	}