<?php

/*
 * Test to rotate image
 */

require __DIR__ . '/../vendor/autoload.php';

use Imanipuly\Imanipuly;

$imanipuly = new Imanipuly('image1.jpg');
$imanipuly->flip(IMG_FLIP_HORIZONTAL);
$imanipuly->save('image1-fliped.jpg');
