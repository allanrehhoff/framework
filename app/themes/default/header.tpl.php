<!DOCTYPE html>
<html>
	<head>
		<title><?php print Registry::get("document")->getTitle(); ?></title>
		<?php foreach(Registry::get("document")->getStylesheets() as $style): ?> 
			<link rel="stylesheet" type="text/css" href="<?php print $style; ?>" media="all" />
		<?php endforeach; ?>
	</head>
	<body>