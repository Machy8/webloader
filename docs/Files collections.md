# Files collections
- Two types: **CSS** and **JS** collections
- Two ways to create them: calling appropriate method or define them in configuration file

## Calling appropriate methods
- createCssFilesCollection
- createJsFilesCollection
- createFilesCollectionsFromArray

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
	
$webLoader->createFilesCollectionsFromArray([
    'admin' => [
        'cssFiles' => ['path/to/some/file.css'],
        'cssFilters' => ['filterName']
    ]
]);

$render = $webloader->render();

echo $render->css('core');
echo $render->js('core');
````

## Configuration file (webloader.neon)
**PHP**
````php
$webLoader->setOutputDir('path/to/output/dir')
    ->createFilesCollectionsFromConfig('path/to/webloader.neon');

$render = $webloader->getFilesCollectionRender();

echo $render->css('core');
echo $render->js('core');
````

**NEON**
````yaml
core:
	jsFiles:
		- path/to/jquery.js
		- path/to/anotherLibrary.js

	cssFiles:
		- path/to/cssFramework.css
		- path/to/anotherStyle.css

	jsFilters:
		- urlFilter
		- minifier

	cssFilters:
		- minifier
````
