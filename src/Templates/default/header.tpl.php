<!DOCTYPE html>
<html>

<head>
	<title><?php print $title ?></title>
	<?php foreach ($stylesheets as $style): ?>
		<link rel="stylesheet" type="text/css" nonce="<?php print $nonce; ?>" href="<?php print $style; ?>" media="all" />
	<?php endforeach; ?>
</head>

<body class="<?php print $bodyClasses; ?>">