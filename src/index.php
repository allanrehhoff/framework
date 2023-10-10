<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* Don't forget to read the documentation.
	*/
	require "startup.php";

	// Global state objects
	\Singleton::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	\Singleton::set(new \Database\Connection(
		\Singleton::getConfiguration()->get("database.host"),
		\Singleton::getConfiguration()->get("database.username"),
		\Singleton::getConfiguration()->get("database.password"),
		\Singleton::getConfiguration()->get("database.name")
	));

	// Other objects
	$iRouter = new \Core\Router(new \Core\Request, new \Core\Response);

	$iApplication = new \Core\Application($iRouter);

	// Render the entire thing
	$iApplication->run()->render();