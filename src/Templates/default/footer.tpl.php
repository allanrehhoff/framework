	<?php foreach ($javascript as $script): ?>
		<script type="text/javascript" nonce="<?php print $nonce; ?>" src="<?php print $script; ?>"></script>
	<?php endforeach; ?>
	</body>
</html>
