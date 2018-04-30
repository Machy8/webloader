# Tracy bridge
- Can be added manually as in the following code
- Shows information about: Files collections, files collections containers, filters, placeholders, output and document root dir path, if cache is enabled and version

````php
$panel = new \WebLoader\Bridges\Tracy\WebLoaderPanel;
$panel->setWebLoader($webLoader->getCompiler());
````

**Nette framework**
Debugger can be switched of by adding `debugger: FALSE` into the webloader configuration section.

````
webloader:
  debugger: FALSE
````
