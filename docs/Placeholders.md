# Placeholders
- Placeholders can be used in paths to configuration files or to css and js files.
- Is possible to set own path placeholder delimiter. Default is `%`.

````php
$webLoader->addPathsPlaceholders([
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
$webLoader->setPathPlaceholderDelimiter('#');
    
$webloader->createJsFilesCollection('core')
    ->setFiles([
        '#jsDir#/script.js'
    ]);
````
