<?php require $header; ?>
	<h1>404 Error.</h1>
	<strong>Was does that mean in a human language?</strong>
	<p>The page you've requested at <em><?php print $_SERVER["REQUEST_URI"]; ?></em> does not exist.</p>
	<strong>What do I do now?</strong>
	<ul>
		<li>Check you haven't mispelled the request URI, (the part after "<?php print $_SERVER["HTTP_HOST"]; ?>")</li>
		<li>Contact us about whether or not the content you're seeking is located elsewhere.</li>
		<li><a href="/">Go back the front page</a></li>
		<?php if(isset($_SERVER["HTTP_REFERER"])): ?>
			<li><a href="<?php print $_SERVER["HTTP_REFERER"]; ?>">Go back to where you came from</a></li>
		<?php endif; ?>
	</ul>
<?php require $footer; ?>