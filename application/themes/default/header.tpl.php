<!DOCTYPE html>
<html>
	<head>
		<title><?php print $title ?></title>
		<?php foreach(\DOM\Document::getStylesheets() as $style): ?>
			<link rel="stylesheet" type="text/css" href="<?php print $style; ?>" media="all" />
		<?php endforeach; ?>
	</head>
	<body>