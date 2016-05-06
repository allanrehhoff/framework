<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/

	require "preprocess.php";

	$config = new \Core\ConfigurationParser();
	$app = new \Core\Application();

	$db = new \Database\DatabaseConnection(
		$config->get("database.host"),
		$config->get("database.name"),
		$config->get("database.username"),
		$config->get("database.password"),
		$config->get("database.debug")
	);

	$controller = $app->getControllerPath($app->arg(0));
	if($controller !== false) {
		require $controller;
	}

	$themeFunctions = $app->getThemePath()."/main-functions.php";
	if(is_file($themeFunctions)) {
		require $themeFunctions;
	}

	$view = $app->getView();
	if($view !== false) {
		require $view;
	}

	if($controller === false && $view === false) {
		require $app->getControllerPath("404");
		require $app->getTemplatePath("404");
	}
?>