<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* Don't forget to read the documentation.
	*
	* @link https://github.com/allanrehhoff/framework
	* @author Allan Thue Rehhoff
	*/
	require "startup.php";

	$args = CLI ? $argv : $_GET;
	$iController = Resource::set(new Core\Application($args))->run();

	extract($iController->getData(), EXTR_SKIP);

	require $iController->getView();