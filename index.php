<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* @author Allan Thue Rehhoff
	* @package Rehhoff_Framework
	*/

	require "preprocess.php";

	$data = Registry::set(new Core\Application())->dispatch();

	extract($data, EXTR_SKIP);
	require Registry::get("Core\Application")->getViewPath();