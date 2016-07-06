<?php require($app->getViewPath('header')); ?>
	<h1>Dammit 404! That page was not found.</h1>
	<strong>Was does that mean in a human language?</strong>
	<p>The page you've requested at <em><?php print $_SERVER["REQUEST_URI"]; ?></em> does not exist.</p>
	<p>You could however try any of the following.</p>
	<ul>
		<li>Check you haven't mispelled the request uri, (the part after "<?php print $_SERVER["HTTP_HOST"]; ?>")</li>
		<li>Contact us about whether or not this page is located elsewhere,</li>
		<li><a href="/">Visit the front page</a></li>
		<?php if($_SERVER["HTTP_REFERER"]): ?>
			<li><a href="<?php print $_SERVER["HTTP_REFERER"]; ?>">Go back to where you came from</a></li>
		<?php endif; ?>
	</ul>
<?php require($app->getViewPath('footer')); ?>