<?php
	class CliController extends \Core\Controller {
		public function index() {
			print "hello from cli".CRLF;
		}

		public function interface() {
			print "Hello from interface".CRLF;
		}
	}