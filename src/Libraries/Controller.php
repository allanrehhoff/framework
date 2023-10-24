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
		 * @var \Core\Renderer
		 */
		protected \Core\Renderer $renderer;

		/**
		 * @var array Child controllers classes to be executed when the main one finalizes.
		 */
		protected array $children = [];

		/**
		 * Controllers should declare this function instead of a constructor.
		 */
		abstract protected function index();

		/**
		 * Extending child controllers must not have a constructor.
		 * @param \Core\Application $iApplication
		 */
		public function __construct(\Core\Application $iApplication) {
			$this->application = $iApplication;

			$this->router = $this->application->getRouter();
			$this->request = $this->router->getRequest();
			$this->response = $this->router->getResponse();
		}

		/**
		 * Notify of backwards compatibility breakages when setting data
		 * When accessing an array using the $this->data["key] = "value" syntax,
		 * it actually goes through __get(); instead of __set();
		 * This is because it is the array stored in $this->data that is being modified rather than the class memb
		 */
		public function __get(string $name) {
			if($name == "data") {
				throw new \RuntimeException("Setting data on controller object is not allowed, use '\$this->response->data[]' instead");
			}

			return $this->$name;
		}

		/**
		 * Function that is called for all requests, before the default controller method.
		 * Constructs the overall environment, setting up helpers and initial variables.
		 * 
		 * @return void
		 */
		final public function start() : void {
			// assets and template properties are
			// set here to enable compatibility with
			// the test suite, placing them inside
			// the below if block will yild errors
			$this->renderer = new \Core\Renderer();
			
			// While it's safe to load these children class in cli
			// there's as of yet, no beneficial reason to.
			// So we'll save the memory and CPU cycles
			if(IS_CLI === false) {
				$this->template = new \Core\Template(new \Core\Assets);

				if($this->getParent() === null) {
					$this->children[] = new \Core\ClassName("Header");
					$this->children[] = new \Core\ClassName("Footer");

					$this->response->setTitle(array_slice($this->request->getArguments(), -1)[0] ?? '');
				}
			}
		}
		
		/**
		 * Method that is called for all requests will be run before sending output
		 * 
		 * @return void
		 */
		final public function stop() : void {

		}

		/**
		 * Extracts controller data property as variables and renders the view
		 * @return void
		 */
		final public function output() : void {
			// Stopping here, will prevent errors such as
			// "Call to a member function getTemplatePath() on null"
			// This is fine as we don't need a view layer for CLI
			if(IS_CLI) exit(0);

			$view = $this->response->getView();

			if(trim($view) === '') {
				throw new \Core\Exception\Governance(sprintf(
					"%s did not set a view for rendering, a view must be set with \$this->response->setView(); or exit(); should be called",
					$this->application->getExecutedClassName()->toString()
				));
			}

			$this->response->sendHttpHeaders();

			$this->renderer->render(
				$this->template->getPath($view), 
				$this->response->getData()
			);
		}

		/**
		 * Set current controllers parent.
		 * 
		 * @param \Controller $iController Controller instance to use as parent.
		 */
		final public function setParent(\Controller $iController) {
			$this->parent = $iController;
		}

		/**
		 * Get current parent controller instance
		 * 
		 * @return \Controller The current parent controller instance, will be null for the root controller.
		 */
		final public function getParent() : ?\Controller {
			return $this->parent;
		}
		
		/**
		 * Get names of children controllers
		 * 
		 * @return array
		 */
		final public function getChildren() : array {
			return $this->children;
		}

		/**
		 * @return \Core\Theme
		 */
		final public function getTemplate() : \Core\Template {
			return $this->theme;
		}

		/**
		 * @return \Core\Application
		 */
		final public function getApplication() : \Core\Application {
			return $this->application;
		}

		/**
		 * @return \Core\Router
		 */
		final public function getRouter() : \Core\Router {
			return $this->router;
		}

		/**
		 * @return \Core\Request
		 */
		final public function getRequest() : \Core\Request {
			return $this->request;
		}

		/**
		 * @return \Core\Response
		 */
		final public function getResponse() : \Core\Response {
			return $this->response;
		}
	}