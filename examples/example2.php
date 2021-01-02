<?php

/*
 * Test to rotate image
 */

require __DIR__ . '/../vendor/autoload.php';

use Imanipuly\Imanipuly;
use Imanipuly\Extension\GdExtension;
use Imanipuly\Extension\ImagickExtension;

$imanipuly = new Imanipuly(new ImagickExtension(), 'examples/image1.jpg');
$imanipuly->resize(500, 500);
//$imanipuly->filter(IMG_FILTER_GRAYSCALE);
$imanipuly->filter(\Imagick::IMGTYPE_GRAYSCALE);
$imanipuly->writeWithFont(
    'Teste 123',
    'examples/IndieFlower.ttf',
    40,
    ['red' => 176, 'green' => 191, 'blue' => 26],
    10,
    10
);
$imanipuly->save('image1-resized.jpg');
