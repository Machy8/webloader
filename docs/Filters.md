# Filters
- Separated for **CSS** and **JS** so they can have the same name
- They are for each loaded file

````php
$webLoader
    ->addCssFilter('urlFilter', function (string $code, string $filePath) {
        // filter url
        return $code;
    })
    ->addJsFilter('minifier', function (string $code) {
        // Minify
        return $code;
    });
````
