# Instanciation

You can create a `QueryString` object from a PSR-7 `UriInterface` object, a string or an array of values.

```php
require_once __DIR__ . '/vendor/autoload.php';

use function BenTools\QueryString\query_string;

// Instanciate from an existing Psr\Http\Message\UriInterface object
$qs = query_string($uri);

// Instanciate from a string
$qs = query_string('foo=bar&baz=bat');
$qs = query_string('?foo=bar&baz=bat'); // This works too

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

## Instanciate from current location

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

## Create your own parser

[bentools/querystring](https://github.com/bpolaszek/querystring) relies on PHP's `parse_str()` function to create an array of parameters from your query string.

This may not fit your needs, because sometimes you have to decode some query strings like this:

> status=foo&status=bar

In this case, `parse_str()` will not consider `status` as an array. You can implement your own `QueryStringParserInterface` to get over this:

```php
use BenTools\QueryString\Parser\QueryStringParserInterface;
use function BenTools\QueryString\query_string;
use function BenTools\QueryString\pairs;

$myParser = new class implements QueryStringParserInterface
{

    public function parse(string $queryString): array
    {
        $params = [];

        foreach (pairs($queryString) as $key => $value) {
            if (isset($params[$key])) {
                $params[$key] = (array) $params[$key];
                $params[$key][] = $value;
            } else {
                $params[$key] = $value;
            }
        }

        return $params;
    }

};

$qs = query_string('foo=bar&foo=baz&baz=bat', $myParser);
print_r($qs->getParams());
/* Array
(
    [foo] => Array
        (
            [0] => bar
            [1] => baz
        )

    [baz] => bat
) */

// You can also set it as the default parser:
QueryString::setDefaultParser($myParser);

// Or restore the native one
QueryString::restoreDefaultParser();
```


[Next](ManipulateParameters.md) - Manipulate parameters