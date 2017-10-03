<?php

require_once 'vendor/autoload.php';

use Tracy\Debugger;

Debugger::$strictMode = TRUE;
Debugger::enable(DEBUGGER::DEVELOPMENT);

$webloader = new \WebLoader\Compiler;
$webloader->setPathsPlaceholders([
	'assetsDir' => __DIR__ . '/assets'
]);
$webloader->createCssFilesCollection('homepage')
	->setFiles([
		__DIR__ . '/assets/core.css',
		__DIR__ . '/assets/style.css'
	]);
$webloader->createCollectionsFromConfig(__DIR__ . '/configs/webloader.neon');

$webloader->setOutputDir(__DIR__ . '/webtemp');
$webloader->compile();

$render = $webloader->getRender();
echo $render->css('homepage', [
	'rel' => 'stylesheet',
	'type' => 'text/css'
], TRUE);
