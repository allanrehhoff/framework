<?php
class CliController extends Controller {
	/**
	 * /cli/index
	 * @return void
	 */
	public function index(): void {
		print "hello from cli" . CRLF;
	}

	/**
	 * /cli/interface
	 * @return void
	 */
	public function interface(): void {
		print "Hello from interface" . CRLF;
	}

	/**
	 * /cli/my-method
	 * @return void
	 */
	public function myMethod(): void {
		print "myMethod was called..";
	}
}
