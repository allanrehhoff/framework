	<?php foreach(\DOM\Document::getStylesheets() as $script): ?>
		<script type="text/javascript" src="<?php print $script; ?>"></script>
	<?php endforeach; ?>
	</body>
</html>