Imanipuly
=========

Library to manipulate image with GD library.

## Installation

```
$ composer require igor822/imanipuly
```

## Example

To start working with Imanipuly

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Imanipuly\Imanipuly;

$imanipuly = new Imanipuly('image.jpg');
$imanipuly->filter(IMG_FILTER_SMOOTH, -4);
$imanipuly->save('image1.jpg');
```
