<h1 align="center">
 <img src="https://github.com/Machy8/webloader/blob/master/logo.png" alt="">
 <br>
 <a href="https://travis-ci.org/Machy8/webloader">
   <img src="https://travis-ci.org/Machy8/webloader.svg?branch=master" alt="">
 </a>
 <a href="https://coveralls.io/github/Machy8/webloader?branch=master">
   <img src="https://coveralls.io/repos/github/Machy8/webloader/badge.svg?branch=master" alt="">
 </a>
 <a href="https://github.com/Machy8/webloader">
   <img src="https://img.shields.io/packagist/dm/machy8/webloader.svg" alt="">
 </a>
 <a href="https://packagist.org/packages/machy8/webloader">
   <img src="https://img.shields.io/packagist/dt/machy8/webloader.svg?style=flat" alt="">
 </a>
</h1>

📦 Simple, easy to use, php bundler for javascript and css.

## In a nutshell
- **Configurable**: in one file (example bellow or in docs)
- Files **[collections](https://github.com/Machy8/webloader/blob/master/docs/Files%20collections.md)** and **[containers](https://github.com/Machy8/webloader/blob/master/docs/Files%20collections%20containers.md)**: makes assets organizing incredibly simple
- **[Filters](https://github.com/Machy8/webloader/blob/master/docs/Filters.md)**: callable in two different ways
- **[Path placeholders](https://github.com/Machy8/webloader/blob/master/docs/Placeholders.md)**: reusable paths to files, less writing
- Allows you to load **remote** and **local** files
- If you have some critical css, you can load it directly into the page with minimal configuration required
- **Prepared for read only deployment** - webloader is able to compile all files collections at once
- **[Debugger bar](https://github.com/Machy8/webloader/blob/master/docs/Tracy%20bridge.md)** for [Tracy](https://tracy.nette.org/cs/)

## Requirements
- PHP 8.0+
- If you use Nette Framework - v2.4+

## Installation
```
 composer require machy8/webloader
```

## Docs
- [Files collections](https://github.com/Machy8/webloader/blob/master/docs/Files%20collections.md)
- [Files collections containers](https://github.com/Machy8/webloader/blob/master/docs/Files%20collections%20containers.md)
- [Filters](https://github.com/Machy8/webloader/blob/master/docs/Filters.md)
- [Placeholders](https://github.com/Machy8/webloader/blob/master/docs/Placeholders.md)
- [Tracy bridge](https://github.com/Machy8/webloader/blob/master/docs/Tracy%20bridge.md)

## Quick start
Let's say we have two css files (**styla-a.css** and **style-b.css**) and we want to bundle them into one file which name will be **my-bundle**. This bundle will be stored in a **webtemp dir** (must be accessible from a browser).

The recommended way to configure Web Loader is through neon configuration files. The first step is to create a bundle.neon.
````yaml
my-bundle:
    cssFiles:
        - path/to/style-a.css
        - path/to/style-b.css
````

Next step is to init Web Loader, set the output dir path and tell him to create bundles from **bundle.neon**.
````PHP
$webloader = new \WebLoader\Engine('path/to/webtemp');
$webloader->createFilesCollectionsFromConfig('path/to/bundle.neon');
````

The last step is to call files collections render to render css files collection named my-bundle.
````PHP
echo $webloader->getFilesCollectionRender()->css('my-bundle');
````

The PHP file after the last edit will looks like this:
````PHP
$webloader = new \WebLoader\Engine('path/to/output/dir');
$webloader->createFilesCollectionsFromConfig('path/to/bundle.neon');

echo $webloader->getFilesCollectionRender()->css('my-bundle');
````

The output will be similiar to the following code:
````html
<link type="text/css" rel="stylesheet" href="/path/to/webtemp/my-bundle.css?v=1512829634">
````

## Quick start (for Nette Framework)
For the Nette Framework it is very similar. First of all, register Web Loader extension.

````yaml
extensions:
    webloader: WebLoader\Bridges\Nette\WebLoaderExtension
````

Next step is to add Web Loader section with my-bundle collection configuration inside.
````yaml
webloader:
    filesCollections:
        my-bundle:
            cssFiles:
                - path/to/style-a.css
                - path/to/style-b.css
````

In your presenter, inject the engine...
````PHP
/**
 * @var Engine
 */
private $webLoader;


public function __construct(\WebLoader\Engine $engine)
{
    $this->webLoader = $engine;
}
````

and set template parameters (for example in the **beforeRender** method).
````PHP
public function beforeRender()
{
    $this->template->webloaderFilesCollectionRender = $this->webLoader->getFilesCollectionRender();
}
````

The last step is to call the render in a latte template.
````LATTE
{$webloaderFilesCollectionRender->css('my-bundle')|noescape}
````
