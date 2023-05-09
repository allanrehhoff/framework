<?php
	/**
	* Main entry point for your application.
	* Consult the README.md file for documentation and usage examples.
	*
	* Don't forget to read the documentation.
	*/
	require "startup.php";

	// Global state objects
	\Resource::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	\Resource::set(new \Database\Connection(
		\Resource::getConfiguration()->get("database.host"),
		\Resource::getConfiguration()->get("database.username"),
		\Resource::getConfiguration()->get("database.password"),
		\Resource::getConfiguration()->get("database.name")
	));

	// Other objects
	$iRequest = new \Core\Request();

	$iRouter = new \Core\Router($iRequest);

	$iApplication = new \Core\Application($iRouter);

	// Render the entire thing
	$iApplication->run()->render();