<?php
	require 'preprocess.php';
	
	$app = new Core\Application();
	
	$controller = $app->getControllerPath($app->arg(0));
	$view = $app->getTemplatePath($app->arg(0));

	$themeFunctions = $app->getThemePath()."/functions.php";
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