<?php declare(strict_types=1);

namespace Imanipuly;

use Imanipuly\Extension\ExtensionInterface;

define('image/gif',  'GIF');
define('image/jpeg', 'JPG');
define('image/jpg',  'JPG');
define('image/pjpeg','JPG');
define('image/png',  'PNG');


/**
 * Class to manipulate image, like resize, fill color, rotate and write at image
 * @author Igor Carvalho <igor822@gmail.com>
 * @version 2.0.0
 * @license MIT License
 */
class Imanipuly
{
    private ExtensionInterface $extension;

    public function __construct(ExtensionInterface $extension, string $fileName = '')
    {
        $this->extension = $extension;
        if ($fileName != '') {
            $this->extension->open($fileName);
        }
    }

    public function blurImage(int $level = 10)
    {
        $this->extension->blurImage($level);
    }
    
    public function filter($filterType, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $this->extension->filter($filterType, $arg1, $arg2, $arg3, $arg4);
    }
    
    public function save($savePath, string $type = 'jpg', int $imageQuality = 100): ExtensionInterface
    {
        return $this->extension->save($savePath, $type, $imageQuality);
    }
    
    public function resize(int $newWidth, int $newHeight, string $option = 'auto'): ExtensionInterface
    {
        return $this->extension->resize($newWidth, $newHeight, $option);
    }

    public function writeWithFont(
        string $string,
        string $font,
        int $fontSize,
        array $color,
        int $xPoint = 0,
        int $yPoint = 0,
        int $gravity = 0
    ): ExtensionInterface {
        return $this->extension->writeWithFont($string, $font, $fontSize, $color, $xPoint, $yPoint, $gravity);
    }
}
