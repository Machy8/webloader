# Tracy bridge
- Can be added manually as in following code.

````php
use WebLoader;

$webLoader = new WebLoader\Compiler;
$panel = new WebLoader\Bridges\Tracy\WebLoaderPanel;
$panel->setWebLoader($webLoader);

````