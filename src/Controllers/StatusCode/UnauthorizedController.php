<?php

namespace StatusCode;

/**
 * Controller for paths that require authentication.
 */
class UnauthorizedController extends \Controller {

	/**
	 * Constructs and indicates the path requires authentication.
	 * Paths are routed here when \Core\StatusCode\Unauthorized is thrown
	 * @return void
	 */
	public function index(): void {
		$this->response->sendHttpCode(\Core\StatusCode\Unauthorized::getHttpCode());

		$this->response->setTitle("Unauthorized");
		$this->response->setView("unauthorized");
	}
}
