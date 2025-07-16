<?php

namespace StatusCode;

class NotAcceptableController extends \Controller {

	/**
	 * Constructs and indicates the request was not acceptable.
	 * Paths are routed here when \Core\StatusCode\NotAcceptable is thrown
	 * This is typically used when the requested resource format is not supported.
	 * 
	 * HTTP response code 406 should be body-less, as it indicates that the server
	 * cannot produce a response matching the list of acceptable values defined in the request's headers.
	 * 
	 * @return void
	 */
	public function index(): void {
		http_response_code(\Core\StatusCode\NotAcceptable::getHttpCode());
		exit;
	}
}
