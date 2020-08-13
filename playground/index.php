<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>
	<?php
		require_once '../vendor/autoload.php';
		// 1. Install dependencies
		// 2. Dump autoload

		$outputDir = './webtemp';
		$documentRoot = '/';
		$publicPathPrefix = '/playground'; // Optional, can be a domain https://static.domain.com
		$webloader = new \WebLoader\Engine($outputDir, $documentRoot, $publicPathPrefix);

		$webloader->createFilesCollectionsFromConfig('./bundles.neon');
		echo $webloader->getFilesCollectionRender()->css('my-bundle');

	?>
</body>
</html>
