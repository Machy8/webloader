# Files collections containers
- Files collections containers wraps multiple files collections so you can organize them and render them in a bit more simple way
- As in files collections, you can create them by calling appropriate methods or create a configuration file.

## Calling appropriate methods
- createFilesCollectionsContainer
- createFilesCollectionsContainersFromArray

````php
$webloader->createFilesCollectionsContainer('homepage')
    ->setCssCollections([
        'homepage',
        'critical'
    ])
    ->setJsCollections([
        'homepage'
    ]);

$render = $webloader->getFilesCollectionsContainerRender()->selectContainer('homepage');

$render->css();
$render->js();
````

## Configuration file (webloader.neon)
**PHP**
````php
$webLoader->createFilesCollectionsContainersFromConfig('path/to/webloader.neon');

$render = $webloader->getFilesCollectionsContainerRender();

echo $render->css('core');
echo $render->js('core');
````

**NEON**
````YAML
core:
    cssCollections:
        - core
        - critical
    jsCollections: 
    - core
````
