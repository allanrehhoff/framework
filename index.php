<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* @author Allan Thue Rehhoff
	*/

	require "preprocess.php";

	$controller = Registry::set(new Core\Application())->dispatch();
	extract($controller->getData(), EXTR_SKIP);

	if($controller->hasView() === true) {
		require $controller->getView();
	}