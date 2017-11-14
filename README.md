[![Latest Stable Version](https://poser.pugx.org/bentools/querystring/v/stable)](https://packagist.org/packages/bentools/querystring)
[![License](https://poser.pugx.org/bentools/querystring/license)](https://packagist.org/packages/bentools/querystring)
[![Build Status](https://img.shields.io/travis/bpolaszek/querystring/master.svg?style=flat-square)](https://travis-ci.org/bpolaszek/querystring)
[![Coverage Status](https://coveralls.io/repos/github/bpolaszek/querystring/badge.svg?branch=master)](https://coveralls.io/github/bpolaszek/querystring?branch=master)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/querystring.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/querystring)
[![Total Downloads](https://poser.pugx.org/bentools/querystring/downloads)](https://packagist.org/packages/bentools/querystring)

# QueryString

A PSR-7 compliant query string manipulator, with **no dependency**. Not even PSR-7, actually.

## Why?

Because I needed an intuitive way to add or remove parameters from a query string, in any project. 

Oh, and, I also wanted that `['foos' => ['foo', 'bar']]` resolved to `foos[]=foo&foos[]=bar` instead of `foos[0]=foo&foos[1]=bar`, unlike many libraries do.

This behavior is not the default one of that library, but there's an [easy way to change it](#change-renderer).

## Usage


**Instanciation**

You can create a `QueryString` object from a string or an array of values.

```php
require_once __DIR__ . '/vendor/autoload.php';

use function BenTools\QueryString\query_string;

// Instanciate from an existing Psr\Http\Message\UriInterface object
$qs = query_string($uri);

// Instanciate from a string
$qs = query_string('foo=bar&baz=bat');

// Instanciate from an array
$qs = query_string(['foo' => 'bar', 'baz' => 'bat']);

// Or create an empty object to get started
$qs = query_string();
```

If you don't like shortcut functions, use the class' factory:
```php
use BenTools\QueryString\QueryString;
$qs = QueryString::factory('foo=bar&baz=bat'); // Same argument requirements
```

**Retrieve all parameters**
```php
print_r($qs->getParams());
/* Array
(
    [foo] => bar
    [baz] => bat
) */
```

**Retrieve specific parameter**
```php
print_r($qs->getParam('foo')); // bar
```

**Add / replace parameter** 
```php
$qs = $qs->withParam('foo', 'foofoo');
print_r($qs->getParams());
/* Array
(
    [foo] => foofoo
    [baz] => bat
) */
print($qs); // foo=foofoo&baz=bat
```

**Remove parameter**
```php
$qs = $qs->withoutParam('baz');
print($qs); // foo=foofoo
```

**Create from a complex, nested array**
```php
$qs = query_string([
    'yummy' => [
        'fruits' => [
            'strawberries',
            'apples',
            'raspberries',
        ],
    ]
]);
```
**Retrieve a parameter at a specific path**
```php
print($qs->getParam('yummy', 'fruits', 2)); // raspberries
```


**Remove a parameter at a specific path**

_Example: remove "apples", resolved at `$params['yummy']['fruits'][1]`_

```php
$qs = $qs->withoutParam('yummy', 'fruits', 1);
print_r($qs->getParams());
/* Array
(
    [yummy] => Array
        (
            [fruits] => Array
                (
                    [0] => strawberries
                    [1] => raspberries // Yep, this indexed array has been reordered.
                )

        )

)*/
```

**Render as string**
```php
print(urldecode((string) $qs)); // yummy[fruits][0]=strawberries&yummy[fruits][1]=raspberries
```
_Tip: you can easily remove numeric indices by [switching to another renderer](#change-renderer) or create yours._

**Change encoding**

This library renders query strings with [RFC 3986](http://www.rfc-base.org/txt/rfc-3986.txt) by default, but you can change it if needed.
```php
$qs = query_string('param=foo bar');
print((string) $qs); // param=foo%20bar

$qs = $qs->withRenderer(
    $qs->getRenderer()->withEncoding(PHP_QUERY_RFC1738)
);
print((string) $qs); // param=foo+bar
```

**Change separator**
```php
$qs = query_string('foo=bar&baz=bat');
$qs = $qs->withRenderer(
    $qs->getRenderer()->withSeparator(';')
);
print((string) $qs); // foo=bar;baz=bat
```

**Instanciate from current location**

It will read `$_SERVER['QUERY_STRING']`.

```php
use function BenTools\QueryString\query_string;
$qs = query_string()->withCurrentLocation();
```
or:
```php
use BenTools\QueryString\QueryString;
$qs = QueryString::createFromCurrentLocation();
```

Of course this will throw a `RuntimeException` when trying to run this from `cli` :smile:

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

## Retrieve key / value pairs

This can be useful if you want to generate hidden input fields based on current query string.

```php
use function BenTools\QueryString\query_string;
use function BenTools\QueryString\withoutNumericIndices;

$qs = query_string(
    'f[status][]=pending&f[status][]=reopened&f[status][]=awaiting', 
    withoutNumericIndices()
);

foreach ($qs->getPairs() as $key => $value) {
    printf(
        '<input type="hidden" name="%s" value="%s"/>' . PHP_EOL, 
        urldecode($key), 
        htmlentities($value)
    );
}
```

Output:
```
<input type="hidden" name="f[status][]" value="pending"/>
<input type="hidden" name="f[status][]" value="reopened"/>
<input type="hidden" name="f[status][]" value="awaiting"/>
```

## Installation
PHP 7.1+ is required.
> composer require bentools/querystring 1.0.x-dev

## Tests
> ./vendor/bin/phpunit

## License
MIT