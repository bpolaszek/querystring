# Render as string

```php
use function BenTools\QueryString\query_string;

$qs = query_string([
    'yummy' => [
        'fruits' => [
            'strawberries',
            'raspberries',
        ],
    ]
]);

print(urldecode((string) $qs)); // yummy[fruits][0]=strawberries&yummy[fruits][1]=raspberries
```
_Tip: you can easily remove numeric indices by [switching to another renderer](#change-renderer) or create yours._

## Change renderer

Remove numeric indices:
```php
use function BenTools\QueryString\withoutNumericIndices;
$qs = $qs->withRenderer(
    withoutNumericIndices()
);
print(urldecode((string) $qs)); // yummy[fruits][]=strawberries&yummy[fruits][]=raspberries
```

Or define it on a global scope for future QueryString objects:
```php
use BenTools\QueryString\QueryString;
use function BenTools\QueryString\withoutNumericIndices;

QueryString::setDefaultRenderer(withoutNumericIndices());
```

You can also create your own rendering logic by implementing `BenTools\QueryString\Renderer\QueryStringRendererInterface`.


## Change encoding

This library renders query strings with [RFC 3986](http://www.rfc-base.org/txt/rfc-3986.txt) by default, but you can change it if needed.
```php
$qs = query_string('param=foo bar');
print((string) $qs); // param=foo%20bar

$qs = $qs->withRenderer(
    $qs->getRenderer()->withEncoding(PHP_QUERY_RFC1738)
);
print((string) $qs); // param=foo+bar
```

## Change separator

```php
$qs = query_string('foo=bar&baz=bat');
$qs = $qs->withRenderer(
    $qs->getRenderer()->withSeparator(';')
);
print((string) $qs); // foo=bar;baz=bat
```


## PSR-7 manipulation
Example:

```php
use function BenTools\QueryString\query_string;

/**
 * @var \Psr\Http\Message\MessageInterface $uri
 */
print((string) $uri); // http://www.example.net/

$uri = $uri->withQuery(
    (string) query_string($uri)->withParam('foo', 'bar')
);

print((string) $uri); // http://www.example.net/?foo=bar
```

[Previous](ManipulateParameters.md) - Manipulate parameters
