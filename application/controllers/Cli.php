<?php
	class CliController extends Controller {
		public function index() {
			print "hello from cli".CRLF;
		}

		public function interface() {
			print "Hello from interface".CRLF;
		}

		public function myMethod() {
			print "myMethod was called..";
		} 
	}