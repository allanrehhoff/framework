<?php

namespace StatusCode;

/**
 * Controller for paths with malformed requests.
 */
class BadRequestController extends \Controller {

	/**
	 * Constructs and indicates the request was malformed.
	 * Paths are routed here when \Core\StatusCode\BadRequest is thrown
	 * @return void
	 */
	public function index(): void {
		$this->response->sendHttpCode(\Core\StatusCode\BadRequest::getHttpCode());

		$this->response->setTitle("Bad Request");
		$this->response->setView("badrequest");
	}
}
