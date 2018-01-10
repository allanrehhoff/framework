<?php
	class CliController extends \Core\Controller {
		public function __construct() {
			parent::__construct();
			print "hello from cli".CRLF;
		}

		public function interface() {
			print "Hello from interface".CRLF;
		}
	}