	<?php foreach(Registry::get("document")->getStylesheets() as $script): ?>
		<script type="text/javascript" src="<?php print $script; ?>"></script>
	<?php endforeach; ?>
	</body>
</html>