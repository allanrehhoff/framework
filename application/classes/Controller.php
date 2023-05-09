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
		protected $title;

		/**
		 * @var array Variable data generated by extending controllers.
		 */
		protected $data = [];

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
		}

		/**
		 * Constructs the overall environment, setting up helpers and initial variables.
		 * 
		 * @return void
		 */
		final public function start() {
			if(IS_CLI === false) {
				$this->assets = new \Core\Assets();
				$this->theme = new \Core\Theme($this->assets);
			}

			if($this->getParent() === null) {
				$this->children[] = new \Core\ClassName("Header");
				$this->children[] = new \Core\ClassName("Footer");

				$this->setTitle(array_slice($this->router->getArgs(), -1)[0]);
			}
		}
		
		/**
		 * Contains accessible theme variables.
		 * 
		 * @return void
		 */
		final public function stop() : void {
			if($this->getParent() === null) {
				$this->data["bodyClasses"] = $this->getBodyClasses();
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

			extract($this->getData(), EXTR_SKIP);

			require $this->getView();
		}

		/**
		* Provides data set by extending controllers.
		* @return array
		*/
		final public function getData() : array {
			return $this->data;
		}

		/**
		 * Set data in current controller
		 * 
		 * @param array $data Array of data to set.
		 * @return void
		 */
		final public function setData(array $data) : void {
			$this->data = $data;
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
		 * Set a dynamic value for the title tag.
		 * 
		 * @param string $title a title to display in a template file.
		 * @return void
		 */
		final public function setTitle(string $title) : void {
			$this->data["title"] = sprintf(\Resource::getConfiguration()->get("base_title"), $title);
		}

		/**
		 * Get the current page title to be displayed.
		 * 
		 * @return string
		 */
		final public function getTitle() : string {
			return $this->data["title"];
		}

		/**
		 * Get the path to a template file, ommit .tpl.php extension
		 * 
		 * @param string $template name of the template file to get path for,
		 * @return string
		 */
		final public function getView(string $template = null) : string {
			if($template === null) {
				$template = $this->data["view"];
			}

			$view = $this->theme->getTemplatePath($template . ".tpl.php");

			return $view;
		}

		/**
		 * Checks if the requested controller has a corresponding view.
		 * 
		 * @return bool
		 */
		final public function hasView() : bool {
			return file_exists($this->getView()) && !IS_CLI;
		}

		/**
		 * Convenience wrapper, for setting/overriding a view within any controller
		 * 
		 * @param string name of the view to use, without .tpl.php extensions.
		 * @return bool
		 */
		final public function setView(string $view) : void {
			$this->data["view"] = $view;
		}

		/**
		 * Determines classes suiteable for the <body> tag
		 * These classes can be used for easier identification of controller and view files used
		 * or CSS styling for specific conditions
		 * 
		 * @return string An escaped string suitable for printing to the 'class' attribute of the <body> tag
		 */
		final public function getBodyClasses() : string {
			$controllerName = $this->getApplication()->getExecutedClassName()->toStringWithoutSuffix();
			$methodName 	= $this->getApplication()->getCalledMethodName()->toStringWithoutSuffix();

			$bodyClasses = [];
			$bodyClasses[] = $controllerName;
			$bodyClasses[] = $controllerName . '-' . $methodName;
			$bodyClasses[] = $this->data["view"];

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
		protected function getTheme() : \Core\Theme {
			return $this->theme;
		}

		/**
		 * @return \Core\Application
		 */
		protected function getApplication() : \Core\Application {
			return $this->application;
		}

		/**
		 * @return \Core\Router
		 */
		protected function getRouter() : \Core\router {
			return $this->router;
		}

		/**
		 * @return \Core\Router
		 */
		protected function getRequest() : \Core\Request {
			return $this->request;
		}
	}