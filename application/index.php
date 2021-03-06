<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* Don't forget to read the documentation.
	*
	* @link https://bitbucket.org/allanrehhoff/framework
	* @author Allan Thue Rehhoff
	*/
	require "startup.php";

	$args = CLI ? $argv : $_GET;
	$controller = Resource::set(new Core\Application($args))->run();

	extract($controller->getData(), EXTR_SKIP);

	require $controller->getView();