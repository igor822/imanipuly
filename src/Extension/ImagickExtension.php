<?php declare(strict_types=1);

namespace Imanipuly\Extension;

class ImagickExtension implements ExtensionInterface
{
    private string $imageName;
    
    private \Imagick $image;
    
    private int $width;
    
    private int $height;
    
    public function open(string $filename): self
    {
        $this->imageName = $filename;
        $this->image = new \Imagick($filename);
        $this->width = $this->image->getImageWidth();
        $this->height = $this->image->getImageHeight();
        
        return $this;
    }

    public function resize(int $newWidth, int $newHeight, string $option = 'auto'): self
    {
        $this->image->resizeImage($newWidth, $newHeight, \Imagick::FILTER_SINC, 0);
        
        return $this;
    }
    
    public function blurImage(int $level = 10): self
    {
        $this->image->blurImage($level, $level);
        
        return $this;
    }
    
    public function save(string $filename, string $type = 'jpg', int $imageQuality = 100): self
    {
        $this->image->writeImage($filename);
        
        return $this;
    }
    
    /**
     * @see https://www.php.net/manual/en/imagick.setimagetype.php 
     */
    public function filter(int $filterType, ?int $arg1 = null, ?int $arg2 = null, ?int $arg3 = null, ?int $arg4 = null): self
    {
        $this->image->setImageType($filterType);
        
        return $this;
    }
    
    public function writeWithFont(
        string $string,
        string $font,
        int $fontSize,
        array $color,
        int $xPoint = 0,
        int $yPoint = 0,
        int $gravity = \Imagick::GRAVITY_CENTER 
    ): self {
        $imagickPixel = new \ImagickPixel(sprintf('rgb(%d, %d, %d)', $color['red'], $color['green'], $color['blue']));
        
        $draw = new \ImagickDraw();
        $draw->setStrokeColor($imagickPixel);
        $draw->setFillColor($imagickPixel);
     
        $draw->setStrokeWidth(0);
        $draw->setFontSize($fontSize);
        $draw->setGravity($gravity);
        
        $draw->setFont($font);
        $this->image->annotateimage($draw, $xPoint, $yPoint, 0, $string);
        
        return $this;
    }

    public function rotate(int $degrees): self
    {
        $this->image->rotateImage(new \ImagickPixel('#00000000'), $degrees);
        
        return $this;
    }

    public function flip($mode = IMG_FLIP_VERTICAL)
    {
        switch ($mode) {
            case IMG_FLIP_VERTICAL:
                $this->image->flipImage();
                break;
            case IMG_FLIP_HORIZONTAL:
                $this->image->transverseImage();
                break;
        }
    }

    public function crop(int $optimalWidth, int $optimalHeight, int $newWidth, int $newHeight): self
    {
        return $this;
    }
}
