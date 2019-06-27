<?php

require __DIR__ . '/../vendor/autoload.php';

use Imanipuly\Imanipuly;

$imanipuly = new Imanipuly('image1.jpg');
$imanipuly->resize(500, 500);
$imanipuly->rotate(180);
$imanipuly->save('image1-rotated.jpg');
