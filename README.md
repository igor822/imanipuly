Imanipuly
=========

Library to manipulate image with GD library.

## Installation

```
$ composer require igor822/imanipuly
```

### Docker build

```
$ docker build -t imanipuly .
```

## Example

To start working with Imanipuly

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Imanipuly\Imanipuly;

$imanipuly = new Imanipuly(new Extensions\ImagickExtension(), 'image.jpg');
$imanipuly->filter(IMG_FILTER_SMOOTH, -4);
$imanipuly->save('image1.jpg');
```

## Execute

Using docker you can execute the examples in `examples/` folder

```
$ docker run -v $(pwd):/var/www -it imanipuly php examples/example1.php
```
