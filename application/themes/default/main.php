<?php
	/**
	* This file should only contain logic which is needed across all pages throughout your website.
	* Before declaring any functions in this file, consider creating them within a class instead.
	*/
	
	\DOM\Document::addJavascript( \DOM\Document::url("/public/stylesheets/jquery-1.11.1.min.js") );
	\DOM\Document::addJavascript( \DOM\Document::url("/test/test.hs"), "head");
	\DOM\Document::addStylesheet( \DOM\Document::url("/public/stylesheets/screen.css") );
?>