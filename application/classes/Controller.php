<?php
	/**
	* The core controller which subcontrollers should extend upon.
	*/
	abstract class Controller {

		/**
		 * @var \Controller Holds the parent controller instance
		 */
		protected $parent = null;

		/**
		 * @var \Core\Request Current request object
		 */
		protected $request;

		/**
		 * @var \Core\Response Current request object
		 */
		protected $response;

		/**
		 * @var \Core\Application The application object
		 */
		protected $application;

		/**
		 * @var \Core\Router Current router object in use
		 */
		protected $router;

		/**
		 * @var \Core\Theme Current theme object
		 */
		protected $theme;

		/**
		 * @var \Core\Assets
		 */
		protected $assets;

		/**
		 * @var string Page title to be displayed
		 */
		//protected $title;

		/**
		 * @var array Variable data generated by extending controllers.
		 */
		//protected $data = [];

		/**
		 * @var array Child controllers classes to be executed when the main one finalizes.
		 */
		protected $children = [];

		/**
		 * Controllers should declare this function instead of a constructor.
		 */
		abstract protected function index();

		/**
		 * Extending child controllers must not have a constructor.
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
		 * Constructs the overall environment, setting up helpers and initial variables.
		 * 
		 * @return void
		 */
		final public function start() : void {
			if(IS_CLI === false) {
				$this->assets = new \Core\Assets();
				$this->theme = new \Core\Theme($this->assets);
			
				if($this->getParent() === null) {
					$this->children[] = new \Core\ClassName("Header");
					$this->children[] = new \Core\ClassName("Footer");

					$this->response->setTitle(array_slice($this->router->getArgs(), -1)[0]);
				}
			}
		}
		
		/**
		 * Contains accessible theme variables.
		 * 
		 * @return void
		 */
		final public function stop() : void {
			if(IS_CLI === false) {
				if($this->getParent() === null) {
					$this->response->data["bodyClasses"] = $this->getBodyTagClasses();
				}
			}
		}

		/**
		 * Extracts controller data property as variables and renders the view
		 * @return void
		 */
		final public function render() : void {
			// Stopping here, will prevent errors such as
			// "Call to a member function getTemplatePath() on null"
			// This is fine as we don't need a view layer for CLI
			if(IS_CLI) exit(0);

			$view = $this->response->getView();

			if(trim($view) === '') {
				throw new \Core\Exception\Governance(sprintf(
					"%s did not set a view for rendering, a view must be set with \$this->response->setView(); or exit(); should be called",
					$this->application->getExecutedClassName()
				));
			}

			$view = $this->theme->getTemplatePath($view);
			$data = $this->response->getData();

			// Closure with a scope for the variable extraction
			$render = function(string $view, array $data) {
				extract($data, EXTR_SKIP);
				require $view;
			};

			$render($view, $data);
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
		 * Determines classes suiteable for the <body> tag
		 * These classes can be used for easier identification of controller and view files used
		 * or CSS styling for specific conditions
		 * 
		 * @return string An escaped string suitable for printing to the 'class' attribute of the <body> tag
		 */
		final public function getBodyTagClasses() : string {
			$controllerName = $this->getApplication()->getExecutedClassName()->toStringWithoutSuffix();
			$methodName 	= $this->getApplication()->getCalledMethodName()->toStringWithoutSuffix();

			$bodyClasses = [];
			$bodyClasses[] = $controllerName;
			$bodyClasses[] = $controllerName . '-' . $methodName;
			$bodyClasses[] = $this->getResponse()->getView();

			foreach($this->getChildren() as $childControllerName) {
				$bodyClasses[] = $childControllerName->toStringWithoutSuffix();
			}

			foreach($this->getRouter()->getArgs() as $arg) {
				$bodyClasses[] = $arg;
			}

			foreach($bodyClasses as $i => $bodyClass) {
				$bodyClasses[$i] = strtolower($bodyClass);
			}

			$classesString = implode(' ', array_unique($bodyClasses));

			return \HtmlEscape::escape($classesString);
		}

		/**
		 * @return \Core\Theme
		 */
		final public function getTheme() : \Core\Theme {
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
		final public function getRouter() : \Core\router {
			return $this->router;
		}

		/**
		 * @return \Core\Router
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