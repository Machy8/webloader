<?php

require_once 'vendor/autoload.php';

use WebLoader\Compiler;
use Tracy\Debugger;

Debugger::$strictMode = TRUE;
Debugger::enable(DEBUGGER::DEVELOPMENT);

$webLoaderPanel = new \WebLoader\Bridges\Tracy\WebLoaderPanel;

$webloader = new Compiler;
$webloader->addPathsPlaceholders([
	'cssFixtures' => __DIR__ . '/tests/fixtures/css'
])
	->setOutputDir('./');
$webloader->createJsFilesCollection('test')
	->setFiles(['%cssFixtures%/style-a.css']);

$webLoaderPanel->setWebLoader($webloader);
echo $webloader->render()->css('test');
