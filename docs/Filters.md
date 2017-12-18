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
````
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
````
$webloader
    ->createCssFilesCollection('myCollection')
    ->setFiles(['somefile.css'])
    ->setFilters(['urlFilter']);

$webloader
    ->createJsFilesCollection('myCollection')
    ->setFiles(['somefile.js'])
    ->setFilters(['minifier']);
````
