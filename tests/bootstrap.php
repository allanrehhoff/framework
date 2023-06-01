<?php
	require __DIR__."/../application/startup.php";

	\Resource::set(new \Configuration(STORAGE . "/config/application.jsonc"));

	ini_set("display_errors", 1);