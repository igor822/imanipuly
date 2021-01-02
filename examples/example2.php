<?php

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
$imanipuly->writeFitWithFont(
    'Se ouvires atentamente a voz do Senhor teu Deus, tendo cuidado de guardar todos os seus mandamentos que eu hoje te ordeno, o Senhor teu Deus te exaltará sobre todas as nações da terra;"',
    'examples/Stylish-Regular.ttf',
    50,
    ['red' => 255, 'green' => 0, 'blue' => 0],
    0,
    50,
    \Imagick::GRAVITY_CENTER
);
$imanipuly->save('image1-gravity.jpg');
