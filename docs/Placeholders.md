# Placeholders
- Placeholders can be used in paths to configuration files or to css and js files

````php
$webLoader->addPathPlaceholders([
    'cssDir' => 'path/to/css/files',
    'jsDir' => 'path/to/js/files',
    'configFiles' => 'path/to/configuration/files'
]);

// Usage
$webloader->createFilesCollectionsFromConfig('%configFiles%/webloader.neon');

$webloader->createCssFilesCollection('core')
    ->setFiles([
        '%cssDir%/style.css'
    ]);
    
$webloader->createJsFilesCollection('core')
    ->setFiles([
        '%jsDir%/script.js'
    ]);
````
