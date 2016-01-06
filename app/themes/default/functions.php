<?php
	/**
	* This file should only contain logic which is needed across all pages throughout your website.
	* Before declaring any functions in this file, consider creating them within a class instead.
	*/
	
	Registry::get("document")->addJavascript( Registry::get("document")->url("/public/stylesheets/jquery-1.11.1.min.js") );
	Registry::get("document")->addJavascript( Registry::get("document")->url("/test/test.hs"), "head");
	Registry::get("document")->addStylesheet( Registry::get("document")->url("/public/stylesheets/screen.css") );
?>