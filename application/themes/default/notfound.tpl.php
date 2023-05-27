<?php require $header; ?>
	<h1>404 Error.</h1>
	<strong>Was does that mean?</strong>
	<p>The page you've requested at <em><?php print $requestUri; ?></em> does not exist.</p>
	<strong>What do I do now?</strong>
	<ul>
		<li>Check you haven't mispelled the request URI, (the part after "<?php print $httpHost; ?>")</li>
		<li>Contact us about whether or not the content you're seeking is located elsewhere.</li>
		<li><a href="/">Go back the front page</a></li>
		<?php if($httpReferer !== ''): ?>
			<li><a href="<?php print $httpReferer; ?>">Go back to where you came from</a></li>
		<?php endif; ?>
	</ul>
<?php require $footer; ?>