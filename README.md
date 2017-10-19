# Web loader
[![Build Status](https://travis-ci.org/Machy8/webloader.svg?branch=master)](https://travis-ci.org/Machy8/webloader)
[![Coverage Status](https://coveralls.io/repos/github/Machy8/webloader/badge.svg?branch=master)](https://coveralls.io/github/Machy8/webloader?branch=master)

📦 Simple, easy to use, php bundler for javascript and css.

## Requirements
- PHP 7.0+
- If you use Nette Framework - v2.3+

## Installation
```
 composer require machy8/webloader
```

## Examples:

**Typical:**
```PHP
$webloader = \WebLoader\Engine;
$webloader->addJsFilter('minifier', function(string $code) {
        // Minify
        return $code;
    })
    ->addPathsPlaceholders([
        'jsDir' => 'path/to/js/dir'
    ]);
    
$webloader->createJsCollection('homepage')
    ->setFiles([
        '%jsDir%/script.js'
    ])
    ->setFilters([
        'minifier'
    ]);
    
echo $webloader->getFilesCollectionRender()->js('homepage', ['async' => TRUE]);
```

**Nette framework:**

Configuration file
````YAML
extensions:
    webloader: WebLoader\Bridges\Nette\WebLoaderExtension
    
webloader:
    outputDir: path/to/webtemp
    filesCollections:
        critical:
            cssFiles:
                - path/to/file.css
            cssLoadContent: TRUE

        homepage:
            cssFiles:
                - path/to/file.css
            cssFilters:
                - urlFilter

            jsFiles:
                - path/to/file.js

    filesCollectionsContainers:
        homepage:
            cssCollections:
                - critical
                - homepage

            jsCollections:
                - homepage
````

Presenter
````PHP
/**
 * @var Engine
 */
private $webLoader;


public function __construct(\WebLoader\Engine $engine)
{
    $this->webLoader = $engine;
}


public function beforeRender()
{
    $this->webLoader
        ->addCssFilter('urlFilter', function(string $code, string $file) {
            $filter = \WebLoader\Filters\CssUrlFilter('path/to/webtemp');
            return $filter->filter($code, $file);
        }, TRUE)
        
        ->addJsFilter('minify', function(string $code) {
            $closureCompiler = new \GoogleClosureCompiler\Compiler;
            $response = $closureCompiler->setJsCode($code)->compile();

            if ($response && $response->isWithoutErrors()) {
                 return $response->getCompiledCode();
            }

            return $code;
        });
       
    $this->template->setParameters([
        'webloaderContainersRender' => $this->webLoader->getFilesCollectionsContainerRender()->selectContainer('homepage')
    ]);
}
````

````LATTE
{$webloaderContainersRender->css()|noescape}
{$webloaderContainersRender->js()|noescape}
````
