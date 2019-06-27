<?php

/*
 * Test to rotate image
 */

require __DIR__ . '/../vendor/autoload.php';

use Imanipuly\Imanipuly;

$imanipuly = new Imanipuly('image1.jpg');
$imanipuly->resize(500, 500);
$imanipuly->filter(IMG_FILTER_GRAYSCALE);
$imanipuly->writeWithFont(
    100,
    100,
    'Teste 123',
    'IndieFlower.ttf',
    40,
    ['red' => 176, 'green' => 191, 'blue' => 26]
);
$imanipuly->save('image1-resized.jpg');
