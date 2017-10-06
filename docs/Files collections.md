# Files collections
- Two types: **CSS** and **JS** collections
- Two methods to create them: calling appropriate method or configure them in configuration file

## Calling appropriate methods
````php
$webloader->setOutputDir('path/to/output/dir');

$webLoader->createCssFilesCollection('core')
	->setFiles([
		'path/to/jquery.js',
		'path/to/anotherLibrary.js'
	])
	->setFilters([
        'minifier'
	]);

$webLoader->createJsFilesCollection('core')
	->setFiles([
	    'path/to/grid-css-framework.css',
    	'path/to/anotherStyle.css'
	])
	->setFilters([
        'minifier'
	]);

$render = $webloader->render();
echo $render->css('core');
echo $render->js('core');
````

## Configuration file (webloader.neon)
**PHP**
````php
$webLoader
	->setOutputDir('path/to/output/dir')
	->createFilesCollectionsFromConfig('path/to/webloader.neon');

$render = $webloader->render();
echo $render->css('core');
echo $render->js('core');
````

**NEON**
````neon
core:
	jsFiles:
		- 'path/to/jquery.js'
		- 'path/to/anotherLibrary.js'
	cssFiles:
		- 'path/to/cssFramework.css'
		- 'path/to/anotherStyle.css'
	jsFilters:
		- minifier
	cssFilters:
		- minifier
````
