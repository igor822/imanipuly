<?php

/*
 * Test to blur image
 */

require __DIR__ . '/../vendor/autoload.php';

use Imanipuly\Imanipuly;
use Imanipuly\Extension\GdExtension;

$imanipuly = new Imanipuly(new GdExtension(), 'examples/image1.jpg');
$imanipuly->blurImage(1);
$imanipuly->filter(IMG_FILTER_SMOOTH, -4);
$imanipuly->blurImage(1);
$imanipuly->save('image1-blurred.jpg');