# Files collections
- Two types: **CSS** and **JS** collections
- Two ways to create them: calling appropriate method or define them in configuration file
- You can load local (path to file) and remote (url) files. The timeout for loading remote files can be set by
the `setRemoteFilesLoadingTimeout` in the compiler class. Default timeout is `15 s`.

## Calling appropriate methods

**Creating files collections**

Collections can be created in several ways
- createCssFilesCollection
- createJsFilesCollection
- createFilesCollectionsFromArray
- createFilesCollectionsFromConfig (path to neon file)

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

$webLoader->createFilesCollectionsFromConfig('path/to/config.neon'); // Example bellow in the Neon section
````

**Generating (with files collection render)**

- Render automatically calls compiler and generates selected element
````PHP
$render = $webloader->getFilesCollectionRender();

// can be array of strings or string results in => <link rel="preload" as="style" href="core.css?v=1520929395">
echo $render->cssPrefetch('core');

// can be array of strings or string results in => <link rel="prefetch" as="style" href="core.css?v=1520929395">
echo $render->cssPreload('core');

echo $render->css('core');

// can be array of strings or string results in => <link rel="preload" as="script" href="core.js?v=1520929395">
echo $render->jsPrefetch('core');

// can be array of strings or string results in => <link rel="prefetch" as="script" href="core.js?v=1520929395">
echo $render->jsPreload('core');

echo $render->js('core');
````

**Manual compiling**

Sometime is required to compile one or all files collections. That ca be done if you access the compiler directly by `$webLoader->getCompiler()` and call appropriate method
- compileAllFilesCollections
- compileCssFilesCollection
- compileJsFilesCollection
- compileFilesCollectionByType

## Configuration file (webloader.neon)
**PHP**
````php
$webLoader->createFilesCollectionsFromConfig('path/to/webloader.neon');

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
        
    jsFilters:
        - urlFilter
        - minifier

    jsOutputElementAttributes:
        async: TRUE

    cssFilters:
        - minifier
   
    cssFiles:
        - path/to/cssFramework.css
        - path/to/anotherStyle.css
        
    cssOutputElementAttributes:
        amp-custom: TRUE
````
