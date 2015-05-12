<?php
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);

	spl_autoload_register(function($class_name) {
		$class_name = str_replace('\\', '/', $class_name);
		
		$class_file = getcwd().'/resources/classes/'.$class_name.'.class.php';

		if(file_exists($class_file)) {
			include $class_file;
		}
	});
	
	use \Application;
	
	$app = new Application\Initialize();
	$db = new Application\SimpleDB(
		$app->config('db_type'),
		$app->config('db_host'),
		$app->config('db_username'),
		$app->config('db_password'),
		$app->config('db_database')
	);
	
	$route = ( (isset($_GET['route']) ) && ($_GET['route'] != '') ) ? $_GET['route'] : $app->config('default_route');

	$app->setArgs($route);
		
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