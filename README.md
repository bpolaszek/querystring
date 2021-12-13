[![Latest Stable Version](https://poser.pugx.org/bentools/querystring/v/stable)](https://packagist.org/packages/bentools/querystring)
[![License](https://poser.pugx.org/bentools/querystring/license)](https://packagist.org/packages/bentools/querystring)
[![CI Workflow](https://github.com/bpolaszek/querystring/actions/workflows/ci-workflow.yml/badge.svg)](https://github.com/bpolaszek/querystring/actions/workflows/ci-workflow.yml)
[![Coverage](https://codecov.io/gh/bpolaszek/querystring/branch/master/graph/badge.svg?token=9AXQUHY1R7)](https://codecov.io/gh/bpolaszek/querystring)
[![Quality Score](https://img.shields.io/scrutinizer/g/bpolaszek/querystring.svg?style=flat-square)](https://scrutinizer-ci.com/g/bpolaszek/querystring)
[![Total Downloads](https://poser.pugx.org/bentools/querystring/downloads)](https://packagist.org/packages/bentools/querystring)

# QueryString

A lightweight, object-oriented, Query String manipulation library.

## Why?

Because I needed an intuitive way to add or remove parameters from a query string, in any project. 

Oh, and, I also wanted that `['foos' => ['foo', 'bar']]` resolved to `foos[]=foo&foos[]=bar` instead of `foos[0]=foo&foos[1]=bar`, unlike many libraries do.

This behavior is not the default one of that library, but there's an [easy way to change it](doc/RenderAsString.md#change-renderer).

## Usage

Simple as that:
```php
require_once __DIR__ . '/vendor/autoload.php';

use function BenTools\QueryString\query_string;

$qs = query_string(
    'foo=bar&baz=bat'
);
$qs = $qs->withParam('foo', 'foofoo')
    ->withoutParam('baz')
    ->withParam('ho', 'hi');

print_r($qs->getParams());
/* Array
(
    [foo] => foofoo
    [ho] => hi
) */

print $qs; // foo=foofoo&ho=hi
```

## Documentation

[Instanciation](doc/Instanciation.md)

[Manipulate parameters](doc/ManipulateParameters.md)

[Render as string](doc/RenderAsString.md)

## Installation
PHP 7.1+ is required.
> composer require bentools/querystring 1.0.x-dev

## Tests
> ./vendor/bin/phpunit

## License
MIT

## See also

[bentools/uri-factory](https://github.com/bpolaszek/uri-factory) - A PSR-7 `UriInterface` factory based on your own dependencies.

[bentools/pager](https://github.com/bpolaszek/bentools-pager) - A simple, object oriented Pager.

[bentools/where](https://github.com/bpolaszek/where) - A framework-agnostic fluent, immutable, SQL query builder.

[bentools/picker](https://github.com/bpolaszek/picker) - Pick a random item from an array, with weight management.

[bentools/psr7-request-matcher](https://github.com/bpolaszek/psr7-request-matcher) - A PSR-7 request matcher interface.

[bentools/cartesian-product](https://github.com/bpolaszek/cartesian-product) - Generate all possible combinations from a multidimensionnal array.

[bentools/string-combinations](https://github.com/bpolaszek/string-combinations) - A string combinations generator.

[bentools/flatten-iterator](https://github.com/bpolaszek/flatten-iterator) - An iterator that flattens multiple iterators or arrays. 
