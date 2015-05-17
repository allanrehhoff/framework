<?php
	require 'core.php';
	
	use \Application;
	
	$app = new Application\Initialize();
	
	$db = new Application\SimpleDB(
		$app->config('database.type'),
		$app->config('database.host'),
		$app->config('database.username'),
		$app->config('database.password'),
		$app->config('database.name')
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