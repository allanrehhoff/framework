<?php

namespace StatusCode;

/**
 * Controller for paths that either cannot be routed.
 * Or when a \Core\Exception\NotFound was thrown
 */
class NotFoundController extends \Controller {

	/**
	 * Constructs an indicates the path wasn't found
	 * @return void
	 */
	public function index(): void {
		$this->response->data["httpHost"] = \Str::safe($this->request->server["HTTP_HOST"] ?? '');
		$this->response->data["requestUri"] = \Str::safe($this->request->server["REQUEST_URI"] ?? '');
		$this->response->data["httpReferer"] = \Str::safe($this->request->server["HTTP_REFERER"] ?? '');

		$this->response->sendHttpCode(\Core\StatusCode\NotFound::getHttpCode());
		$this->response->setTitle("Not found");
		$this->response->setView("notfound");
	}
}
