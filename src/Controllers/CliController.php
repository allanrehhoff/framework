<?php
class CliController extends Controller {
	/**
	 * /cli/index
	 * @return void
	 */
	public function index(): void {
		IS_CLI or throw new \Core\StatusCode\NotFound;
		print "hello from cli" . CRLF;
	}

	/**
	 * /cli/interface
	 * @return void
	 */
	public function interface(): void {
		IS_CLI or throw new \Core\StatusCode\NotFound;
		print "Hello from interface" . CRLF;
	}

	/**
	 * /cli/my-method
	 * @return void
	 */
	public function myMethod(): void {
		IS_CLI or throw new \Core\StatusCode\NotFound;
		print "myMethod was called..";
	}
}
