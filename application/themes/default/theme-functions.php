<?php
	/**
	* This file should only contain logic which is needed across all pages throughout your website.
	* Before declaring any functions in this file, consider creating them within a class instead.
	*
	* I have yet to come up with a proper way of doing this, and preventing you (the developer)
	* from writing complete spaghetti, so please! try not to bloat this file.
	*/

	\DOM\Document::addStylesheet( Tools::url("/application/themes/default/stylesheets/screen.css") );
	\DOM\Document::addStylesheet( Tools::url("/application/themes/default/stylesheets/responsive.css") );

	\DOM\Document::addJavascript( Tools::url("/application/themes/default/javascript/jquery-1.12.3.min.js") );
?>