# Web loader
## Requirements
- PHP 7.0+
- If you use Nette Framework - v2.3+

## Installation
**1 - Download the Web loader using composer:**
```
 composer require machy8/webloader
```

**2 - Usage:**

*Typical:*

```php
$webloader = \WebLoader\Compiler;
$webloader->addJsFilter('minifier', function(string $code) {
        // Minify
        return $code;
    })
    ->addPathPlaceholders([
        'jsDir' => 'path/to/js/dir'
    ]);
    
$webloader->createJsCollection('homepage')
    ->setFiles([
        '%jsDir%/script.js'
    ])
    ->setFilters([
        'minifier'
    ]);
    
echo $webloader->render()->js('homepage', ['async' => TRUE']);
```

*Nette framework:*
- TODO
