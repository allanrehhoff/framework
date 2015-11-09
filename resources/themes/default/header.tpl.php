<!DOCTYPE html>
<html>
	<head>
		<title><?php print $app->getTitle(); ?></title>
		<?php foreach($app->document->getStylesheets() as $style): ?> 
			<link rel="stylesheet" type="text/css" href="<?php print $style; ?>" media="all" />
		<?php endforeach; ?>
	</head>
	<body>