<?php
	//Core settings
	$config = array();
	$config['theme'] = 'default';
	$config['base_title'] = '%s - '; //Only one string parameter supported.
	$config['default_route'] = 'index';
	
	//Database settings
	$config['db_type'] = \Application\SimpleDB::MYSQL;
	$config['db_host'] = 'localhost';
	$config['db_username'] = '';
	$config['db_password'] = '';
	$config['db_database'] = '';
	
?>