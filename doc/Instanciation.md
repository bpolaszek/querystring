# Instanciation

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


[Next](ManipulateParameters.md) - Manipulate parameters