<?php
	DOM\Document::addJavascript( Helper::url("/public/stylesheets/jquery-1.11.1.min.js") );
	DOM\Document::addJavascript( Helper::url("/test/test.hs"), "head");


	DOM\Document::addStylesheet( Helper::url("/public/stylesheets/screen.css") );
?>