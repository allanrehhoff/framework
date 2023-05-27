<?php
	/**
	 * Controller for paths that either cannot be routed.
	 * Or when a \Core\Exception\NotFound was thrown
	 */
	class NotFoundController extends Controller {
		/**
		 * Constructs an indicates the path wasn't found
		 */
		public function index() {
			if(IS_CLI) exit(1);

			$this->response->data["httpHost"] = \HtmlEscape::escape($this->request->server["HTTP_HOST"] ?? '');
			$this->response->data["requestUri"] = \HtmlEscape::escape($this->request->server["REQUEST_URI"] ?? '');
			$this->response->data["httpReferer"] = \HtmlEscape::escape($this->request->server["HTTP_REFERER"] ?? '');

			$this->response->setTitle("Not found");
			$this->response->setView("notfound");
		}
	}