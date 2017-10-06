# Filters
- Separated for **CSS and **JS** so they can have the same name
- They are called after joining all files of processed collection

````php
$webLoader
    ->addCssFilter('minifier', function (string $code) {
        // Minify
        return $code;
    })
    ->addJsFilter('minifier', function (string $code) {
        // Minify
        return $code;
    });
````