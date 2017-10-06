# Tracy bridge
- Can be added manually as in the following code
- Shows information about: Files collections, filters, placeholders, output dir path, if cache is enabled and version

````php
use WebLoader;

$webLoader = new WebLoader\Compiler;
$panel = new WebLoader\Bridges\Tracy\WebLoaderPanel;
$panel->setWebLoader($webLoader);

````