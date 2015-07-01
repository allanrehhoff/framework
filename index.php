<?php
	require 'preprocess.php';
	
	$app = new Application\Initialize();
	
	$db = new Application\DbConnection(
		$app->config('database.host'),
		$app->config('database.name'),
		$app->config('database.username'),
		$app->config('database.password'),
		$app->config('database.debug')
	);

	$controller = $app->getControllerPath($app->arg(0));
	$view = $app->getTemplatePath($app->arg(0));
	
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