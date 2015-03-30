<?php require($app->getTemplatePath('header')); ?>
	<h1>Dang it!</h1>
	<h2>404 error</h2>
	<?php Debug::getTrace(); ?>
<?php require($app->getTemplatePath('footer')); ?>