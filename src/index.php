<?php
	/**
	* Main entry point for your application.
	* Don't forget to read the documentation.
	*/

	// Startup and autoloder
	require "Bootstrap/startup.php";

	// Register events
	require "Bootstrap/events.php";

	// Global state objects
	\Registry::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	\Registry::set(new \Environment(APP_PATH . "/.env"));

	\Registry::set(new \Database\Connection(
		\Registry::getConfiguration()->get("database.host"),
		\Registry::getConfiguration()->get("database.username"),
		\Registry::getConfiguration()->get("database.password"),
		\Registry::getConfiguration()->get("database.name")
	));

	// Trigger init event
	\Core\Event::trigger("application.init");

	// Other objects
	$iRouter = new \Core\Router(new \Core\Request, new \Core\Response);

	$iApplication = new \Core\Application($iRouter);

	// Render the entire thing
	$iApplication->run()->output();