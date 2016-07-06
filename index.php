<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/

	require "preprocess.php";

	$app = \Core\Application::getInstance();
	$db = new \Database\DatabaseConnection(
		\Core\ConfigurationParser::getInstance()->get("database.host"),
		\Core\ConfigurationParser::getInstance()->get("database.username"),
		\Core\ConfigurationParser::getInstance()->get("database.password"),
		\Core\ConfigurationParser::getInstance()->get("database.name")
	);

	$controller = is_file($app->getControllerPath()) ? $app->getControllerPath() : false;
	if($controller !== false) {
		require $controller;
	}

	$themeFunctions = is_file($app->getThemePath()."/main-functions.php") ? $app->getThemePath()."/main-functions.php" : false;
	if($themeFunctions !== false) {
		require $themeFunctions;
	}

	$view = is_file($app->getViewPath()) ? $app->getViewPath() : false;
	if($view !== false) {
		require $view;
	}

	if($controller == false && $view === false) {
		require $app->getControllerPath("404");
		require $app->getViewPath("404");
	}