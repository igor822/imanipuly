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
$imanipuly->filter(\Imagick::IMGTYPE_GRAYSCALE);
$imanipuly->writeWithFont(
    'Something here',
    'examples/IndieFlower.ttf',
    40,
    ['red' => 0, 'green' => 0, 'blue' => 0],
    10,
    10,
    \Imagick::GRAVITY_SOUTHWEST
);
$imanipuly->writeWithFont(
    'Watermark',
    'examples/Stylish-Regular.ttf',
    20,
    ['red' => 0, 'green' => 255, 'blue' => 0],
    10,
    10,
    \Imagick::GRAVITY_SOUTHEAST
);
$imanipuly->writeWithFont(
    'Lorem Ipsum',
    'examples/Stylish-Regular.ttf',
    20,
    ['red' => 255, 'green' => 0, 'blue' => 0],
    10,
    10,
    \Imagick::GRAVITY_CENTER
);
$imanipuly->save('image1-gravity.jpg');
