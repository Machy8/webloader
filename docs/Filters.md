# Filters
- Separated for **CSS** and **JS** so they can have the same name.
- They can be initialized for each file in files collection or once for whole collection after all files are loaded.
- Is possible to check whether the filter is already defined

**Filters definition**
````php
$webLoader ->addCssFilter('urlFilter', function (string $code, string $filePath) {
    // filter url
    return $code;
});

if ( ! $webLoader->getCompiler()->filterExists(Engine::JS, 'minifier') {
    // If you want to run filter for each file of file collection separatelly, set third parameter to TRUE
    $webloader->addJsFilter('minifier', function (string $code) {
        // Minify
        return $code;
    }, TRUE);
}
````

**Usage (configuration in a NEON file)**
````YAML
myCollection:
    cssFiles:
        - somefile.css
    cssFilters:
        - urlFilter

    jsFiles:
        - somefile.js
    jsFilters:
        - minifier
````

**Usage (in pure PHP)**
````PHP
$webloader
    ->createCssFilesCollection('myCollection')
    ->setFiles(['somefile.css'])
    ->setFilters(['urlFilter']);

$webloader
    ->createJsFilesCollection('myCollection')
    ->setFiles(['somefile.js'])
    ->setFilters(['minifier']);
````

##Default filters
There are two filters that comes with webloader.

###Url filter
This filter modifies url in css files according to output directory for correct assets loading. Is recommended to run it for each file separatelly.

````PHP
$webloader->addCssFilter('urlFilter', function ($code, $filePath) use ($outputDir, $documentRoot) {
    $filter = new CssUrlFilter($outputDir, $documentRoot);
    return $filter->filter($code, $filePath);
}, TRUE);
````

###Breakpoints filter
This filter extracts css from output files and creates new files with defined prefixes and put the correct css inside. Is recommended to run it for the whole collection. You can add filter for each file that is generated in the Breakpoints filter.

````PHP
$webloader->addCssFilter('cssBreakpointsFilter', function ($code, $collectionPath) use ($cssMinifier) {
    $breakpoints = [
        'medium' => ['px' => [640, 1023]], // For breakpoints between 640px to 1023px
        'large' => ['*'] // For every other breakpoints
    ];

    $filter = new CssBreakpointsFilter($breakpoints);
    $filter->addOutputFilesFilter(function ($code) use ($cssMinifier) {
        return $cssMinifier->run($code);
    });
    return $filter->filter($code, $collectionPath);
});
````
