# Filters
- Separated for **CSS** and **JS** so they can have the same name.
- They can be initialized for each file in files collection or once for whole collection after all files are loaded.

````php
$webLoader
    ->addCssFilter('urlFilter', function (string $code, string $filePath) {
        // filter url
        return $code;
    })
    
    // If you want to run filter for each file of file collection separatelly, set third parameter to TRUE
    ->addJsFilter('minifier', function (string $code) {
        // Minify
        return $code;
    }, TRUE);
````
