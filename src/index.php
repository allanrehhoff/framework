<?php

/**
 * Main entry point for your application.
 * Don't forget to read the documentation.
 */

// Bootstrap
require "Libraries/Bootstrap/Bootstrap.php";

(new \Bootstrap\Bootstrap)->startup();

// Events
\Core\Event::trigger("core.global.init");

// Handle request
$iRouter = new \Core\Router(new \Core\Request, new \Core\Response);

$iApplication = new \Core\Application($iRouter);

$iApplication->run()->output();
