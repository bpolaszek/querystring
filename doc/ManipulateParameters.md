# Manipulate parameters

**Retrieve all parameters**
```php
use function BenTools\QueryString\query_string;

$qs = query_string(
    'foo=bar&baz=bat'
);

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



[Previous](Instanciation.md) - Instanciation

[Next](RenderAsString.md) - Render as string
