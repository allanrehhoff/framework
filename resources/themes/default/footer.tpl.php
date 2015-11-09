	<?php foreach($app->document->getJavascript() as $script): ?>
		<script type="text/javascript" src="<?php print $script; ?>"></script>
	<?php endforeach; ?>
	</body>
</html>