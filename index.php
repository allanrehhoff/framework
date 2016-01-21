<?php
	require 'preprocess.php';

	$app = new \Core\Application();
	$db = new \Database\DbConnection(
		\Core\ConfigurationParser::getInstance()->get("database.host"),
		\Core\ConfigurationParser::getInstance()->get("database.name"),
		\Core\ConfigurationParser::getInstance()->get("database.username"),
		\Core\ConfigurationParser::getInstance()->get("database.password"),
		\Core\ConfigurationParser::getInstance()->get("database.debug")
	);

	$controller = $app->getControllerPath($app->arg(0));
	$view = $app->getTemplatePath($app->arg(0));

	$themeFunctions = $app->getThemePath()."/main.php";
	if(is_file($themeFunctions)) {
		require $themeFunctions;
	}

	if(!is_file($controller) && !is_file($view)) {
		$controller = $app->getControllerPath('404');
		$view = $app->getTemplatePath('404');
	}

	if(is_file($controller)) {
		require $controller;
	}

	if(is_file($view)) {
		require $view;
	}
?>