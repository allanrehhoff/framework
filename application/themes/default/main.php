<?php
	/**
	* This file should only contain logic which is needed across all pages throughout your website.
	* Before declaring any functions in this file, consider creating them within a class instead.
	*
	* I have yet to come up with a proper way of doing this, and preventing you (the developer)
	* from writing complete spaghetti, so please! try not to bloat this file.
	*/
	
	\DOM\Document::addJavascript( \DOM\Document::url("/public/stylesheets/jquery-1.11.1.min.js") );
	\DOM\Document::addJavascript( \DOM\Document::url("/test/test.hs"), "head");
	\DOM\Document::addStylesheet( \DOM\Document::url("/public/stylesheets/screen.css") );
?>