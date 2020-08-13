# Custom public path

In case, you want to have a domain in the path to bundled files or a some path prefix, you can use public path prefix.

```php
$outputDir = '/webtemp';
$documentRoot = '/';
$publicPathPrefix = '/playground'; // Optional, can be a domain https://static.domain.com
$webloader = new \WebLoader\Engine($outputDir, $documentRoot, $publicPathPrefix);
```

Output:
```html
<link type="text/css" rel="stylesheet" href="/playground/webtemp/my-bundle.css?v=1597237456">
```

Output example, if you use a domain name:
```html
<link type="text/css" rel="stylesheet" href="https://static.domain.com/webtemp/my-bundle.css?v=1597237456">
```
