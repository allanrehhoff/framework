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

	require "preprocess.php";

	$args = CLI ? $argv : $_GET;
	$controller = Registry::set(new Core\Application($args))->dispatch();

	extract($controller->getData(), EXTR_SKIP);

	if($controller->hasView() === true) {
		require $controller->getView();
	}