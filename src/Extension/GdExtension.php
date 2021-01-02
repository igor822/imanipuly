<?php

namespace Imanipuly\Extension;

class GdExtension implements ExtensionInterface
{
    /**
     * Image path name
     * @var string
     */
    private ?\GdImage $image = null;

    /**
     * Width of image
     * @var integer
     */
    private int $width = 0;

    /**
     * Height of image
     * @var integer
     */
    private int $height = 0;

    /**
     * Width of image
     * @var integer
     */
    private int $newWidth = 0;

    /**
     * Height of image
     * @var integer
     */
    private int $newHeight = 0;

    /**
     * Extension of image
     * @var string
     */
    private string $extension = '';

    /**
     * Image to be manilulated, more expecific resized
     * @var object
     */
    private ?object $imageResized = null;

    /**
     * Array of information of image, dimensions and mime type
     * @var array
     */
    private array $imageInfo = [];

    /**
     * Array of max sizes of image to resize
     * @var array
     */
    private array $scale = [];

    /**
     * Record image name
     * @var string
     */
    private string $imageName = '';
    
    /**
     * Open image to initializethe process
     * @param $fileName
     * @return void
     */
    public function open(string $filename): self
    {
        $this->imageName = $filename;
        
        $this->imageInfo = $this->getImageInfo($filename);
        $this->extension = constant($this->imageInfo['mime']);
        
        $this->image = $this->load($filename);

        $this->width = $this->getWidth($this->image);
        $this->height = $this->getHeight($this->image);

        return $this;
    }
    
    /**
     * Load image to be manipulated, extensions supported, jpg, png and gif
     * @param string $image
     * @return object
     */
    public function load(string $image)
    {
        switch ($this->extension) {
            case 'JPG':
                $img = \imagecreatefromjpeg($image);
                break;
            case 'GIF':
                $img = \imagecreatefromgif($image);
                break;
            case 'PNG':
                $img = \imagecreatefrompng($image);
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }
    
    /**
     * Clean all objects attributes
     * @return void
     */
    public function clean(): self
    {
        $this->image = null;
        $this->width = 0;
        $this->height = 0;
        $this->extension = '';
        $this->imageResized = null;
        $this->imageInfo = array();
        $this->scale = array();

        return $this;
    }
    
    /**
     * Init object
     * @return void
     */
    public function init(string $image = ''): self
    {
        $this->clean();
        $this->imageName = (isset($image) && $image != '' ? $image : $this->imageName);
        $this->imageInfo = $this->getImageInfo($this->imageName);
        $this->extension = constant($this->imageInfo['mime']);
        
        $this->image = $this->load($this->imageName);

        $this->width = $this->getWidth($this->image);
        $this->height = $this->getHeight($this->image);

        return $this;
    }

    /**
     * Fill background color, with rgb code
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return void
     */
    public function fillColor(int $red = 0, int $green = 0, int $blue = 0): self
    {
        $image = $this->getImage();
        $color = imagecolorallocate($image, $red, $green, $blue);
        imagefill($image, 0, 0, $color);

        return $this;
    }

    /**
     * Rezise image with some options to bo readed
     * Options (auto, portrait, scale, exact)
     * @param integer $newWidth
     * @param integer $newHeight
     * @param integer $option
     * @return void
     */
    public function resize(int $newWidth, int $newHeight, string $option = 'auto'): self
    {
        if ($option == 'scale') {
            $this->setMaxSizes($newWidth, $newHeight);
        }
        $optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));

        $this->newWidth = $optimalWidth = $optionArray['optimalWidth'];
        $this->newHeight = $optimalHeight = $optionArray['optimalHeight'];

        $this->imageResized = imagecreatetruecolor((int) $optimalWidth, (int) $optimalHeight);
        imagecopyresampled(
            $this->imageResized,
            $this->image,
            0,
            0,
            0,
            0,
            (int) $optimalWidth,
            (int) $optimalHeight,
            (int) $this->width,
            (int) $this->height
        );

        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }

        return $this;
    }

    // TODO Fix method to render transparent border and read diverent corners
    /**
     * Round corners, fill corners color border
     * @param $radius
     * @param $colour
     * @return void
     */
    public function radius(int $radius = 20): self
    {
        $cornerImage = imagecreatetruecolor($radius, $radius);
        $black = imagecolorallocate($cornerImage, 0, 0, 0);
        $solid_colour = ImageColorAllocate($cornerImage, 255, 255, 255);
        $transparentColor = imagecolorallocate($cornerImage, 255, 0, 0);
        imagecolortransparent( $cornerImage, $transparentColor );
        imagefill($cornerImage, 0, 0, $solid_colour);
        imagefilledellipse($cornerImage, $radius, $radius, $radius * 2, $radius * 2, $black);
        
        // TOP-LEFT
        imagecolortransparent($cornerImage, $transparentColor);
        imagecopymerge($this->getImage(), $cornerImage, 0, 0, 0, 0, $radius, $radius, 100);
        
        // TOP-RIGHT
        $cornerImage = imagerotate( $cornerImage, 90, 0 );
        imagecolortransparent($cornerImage, $transparentColor);
        imagecopymerge( $this->getImage(), $cornerImage, 0, $this->height - $radius, 0, 0, $radius, $radius, 100);
        
        // BOTTOM-LEFT
        $cornerImage = imagerotate( $cornerImage, 90, 0 );
        imagecolortransparent($cornerImage, $transparentColor);
        imagecopymerge($this->getImage(), $cornerImage, $this->width - $radius, $this->height - $radius, 0, 0, $radius, $radius, 100);
        
        // BOTTOM-RIGHT
        $cornerImage = imagerotate( $cornerImage, 90, 0 );
        imagecolortransparent($cornerImage, $transparentColor);
        imagecopymerge( $this->getImage(), $cornerImage, $this->width - $radius, 0, 0, 0, $radius, $radius, 100 );             

        return $this;
    }
    
    /**
     * Get dimensions by option selected
     * @param integer $newWidth
     * @param integer $newHeight
     * @param string  $option
     * @return array
     */
    private function getDimensions(int $newWidth, int $newHeight, string $option): array
    {
        switch($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
            break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
            break;
            case 'scale':
                $scale = min($this->scale['maxWidth'] / $this->width, $this->scale['maxHeight'] / $this->height);
                $optimalWidth = floor($scale * $this->width);
                $optimalHeight = floor($scale * $this->height);
            break;
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * Get height size defined by width
     * @param integer $newHeight
     * @return integer
     */
    private function getSizeByFixedHeight(int $newHeight): float
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;

        return $newWidth;
    }

    /**
     * Get width size defined by height
     * @param integer $newWidth
     * @return integer
     */
    private function getSizeByFixedWidth(int $newWidth): float
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;

        return $newHeight;
    }

    /**
     * Get width and height defined by actual dimensions
     * @param integer $newWidth
     * @param integer $newHeight
     * @return array
     */
    private function getSizeByAuto(int $newWidth, int $newHeight): array
    {
        if ($this->height < $this->width) {
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } else if ($this->height > $this->width) {
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * Get sizes by crop mode
     * @param integer $newWidth
     * @param integer $newHeight
     * @return array
     */
    private function getOptimalCrop(int $newWidth, int $newHeight)
    {
        $heightRatio = $this->height / $this->width;
        $widthRatio = $this->width / $this->height;

        $optimalRatio = $widthRatio;
        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        }
        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth  = $this->width  / $optimalRatio;
         
        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    /**
     * Method defined to crop image by dimensions seted
     * @param integer $optimalWidth
     * @param integer $optimalHeight
     * @param integer $newWidth
     * @param integer $newHeight
     * @return void
     */
    public function crop(int $optimalWidth, int $optimalHeight, int $newWidth, int $newHeight): self
    {
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->getImage();

        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->getImage(), $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);

        return $this;
    }

    /**
     * Method defined to save image at the path and quality setting
     * @param integer $savePath
     * @param integer $imageQuality
     * @return void
     */
    public function save($savePath, string $type = 'jpg', int $imageQuality = 100): self
    {
        switch (strtoupper($type)) {
            case 'JPG':
                if(imagetypes() & IMG_JPG) {
                    imagejpeg($this->getImage(), $savePath, $imageQuality);
                }
            break;
            case 'GIF':
                if(imagetypes() & IMG_GIF) {
                    imagegif($this->getImage(), $savePath);
                }
            break;
            case 'PNG':
                $scaleQuality = round(($imageQuality/100) * 9);
                $invertScaleQuality = 9 - $scaleQuality;
                if(imagetypes() & IMG_PNG) {
                    imagepng($this->getImage(), $savePath, $invertScaleQuality);
                }
            break;
        }
        imagedestroy($this->getImage());

        return $this;
    }

    /**
     * Show image at broser without save file
     * @return void
     */
    public function show()
    {
        header('Content-type: '.$this->imageInfo['mime']);
        switch ($this->extension) {
            case 'JPG':
                imagejpeg($this->getImage());
                break;
            case 'GIF':
                imagegif($this->getImage());
                break;
            case 'PNG':
                imagepng($this->getImage());
                break;
        }

        return $this;
    }

    /**
     * Get RGB color at determinated pixel (x and y coordinates)
     * @param integer $xPoint
     * @param integer $yPoint
     * @return array
     */
    public function getPixelColor(int $xPoint, int $yPoint): self
    {
        $rgb = imagecolorat($this->getImage(), $xPoint, $yPoint);
        $colors = imagecolorsforindex($this->getImage(), $rgb);

        return $colors;
    }

    /**
     * Insert some string at image point
     * @param integer $xPoint
     * @param integer $yPoint
     * @param string $string
     * @param integer $fontSize
     * @param array $color
     * @return void
     */
    public function write(string $string, int $fontSize, array $color, int $xPoint = 0, int $yPoint = 0): self
    {
        $textColor = imagecolorallocate($this->getImage(), $color['red'], $color['green'], $color['blue']);
        imagestring($this->getImage(), $fontSize, $xPoint, $yPoint, $string, $textColor);

        return $this;
    }

    /**
     * Insert some string at image point with font
     * @param integer $xPoint
     * @param integer $yPoint
     * @param string $string
     * @param integer $fontSize
     * @param array $color
     * @return void
     */
    public function writeWithFont(
        string $string,
        string $font,
        int $fontSize,
        array $color,
        int $xPoint = 0,
        int $yPoint = 0
    ): self {
        $textColor = imagecolorallocate($this->getImage(), $color['red'], $color['green'], $color['blue']);
        imagettftext($this->getImage(), $fontSize, 0, $xPoint, $yPoint, $textColor, $font, $string);

        return $this;
    }

    /**
     * Get image with grey or colors RGB channels defined
     * @param string $channel
     * @param string $type
     * @return void
     */
    public function separeChannels($channel, $type = 'grey'): self
    {
        for ($i = 0; $i < $this->width; $i++) {
            for($ii = 0; $ii < $this->height; $ii++) {
                $rgb = imagecolorat($this->getImage(), $i, $ii);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8)  & 0xFF;
                $b = $rgb & 0xFF;
                switch($channel) {
                    case 'red':
                        $new = $r;
                        $px = imagecolorallocate($this->getImage(), $r, 0, 0);
                    break;
                    case 'green':
                        $new = $g;
                        $px = imagecolorallocate($this->getImage(), 0, $g, 0);
                    break;
                    case 'blue':
                        $new = $b;
                        $px = imagecolorallocate($this->getImage(), 0, 0, $b);
                    break;
                    case 'none':
                        $new = round(($r + $g + $b) / 3);
                    break;
                }
                if ($type == 'grey') {
                    $px = imagecolorallocate($this->getImage(), $new, $new, $new);
                }
                imagesetpixel($this->getImage(), $i, $ii, $px);
            }
        }

        return $this;
    }

    /**
     * Method defined to rotate image
     * @param integer $degrees
     * @return void
     */
    public function rotate(int $degrees): self
    {
        $image = $this->getImage();
        $this->imageResized = imagerotate($image, $degrees, 0);

        return $this;
    }

    /**
     * Method define to get actual image manipulating inside object
     * @return object
     */
    private function getImage()
    {
        $image = $this->image;
        if (isset($this->imageResized)) {
            $image = $this->imageResized;
        }

        return $image;
    }

    public function flip($mode = IMG_FLIP_VERTICAL)
    {
        imageflip($this->getImage(), $mode);
    }

    /**
     * Get width of image
     * @param string $image
     * @return integer
     */
    public function getWidth($image)
    {
        return imagesx($image);
    }

    /**
     * Get height of image
     * @param string $image
     * @return integer
     */
    public function getHeight($image)
    {
        return imagesy($image);
    }

    /**
     * Get array of image info, like dimensions and mime-type
     * @param string $image
     * @return array
     */
    private function getImageInfo($image)
    {
        return getimagesize($image);
    }

    /**
     * Determine max sizes to resize image
     * @param integer $maxWidth
     * @param integer $maxHeigh
     * @return void
     */
    public function setMaxSizes($maxWidth = 0, $maxHeight = 0)
    {
        $this->scale['maxWidth'] = ($maxWidth > 0) ? $maxWidth : $this->width;
        $this->scale['maxHeight'] = ($maxHeight > 0) ? $maxHeight : $this->height;

        return $this;
    } 

    /**
     * @see (http://www.php.net/manual/en/function.imagefilter.php)
     * @param int $fileType
     * @param int $arg1
     * @param int $arg2
     * @param int $arg3
     * @param int $arg4
     */
    public function filter($filterType, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        if ($filterType == IMG_FILTER_COLORIZE) {
            imagefilter($this->getImage(), $filterType, $arg1, $arg2, $arg3, $arg4);
        } elseif ($filterType == IMG_FILTER_BRIGHTNESS || $filterType == IMG_FILTER_CONTRAST || $filterType == IMG_FILTER_SMOOTH) {
            imagefilter($this->getImage(), $filterType, $arg1);
        } elseif ($filterType == IMG_FILTER_PIXELATE) {
            imagefilter($this->getImage(), $filterType, $arg1, $arg2);
        } else {
            imagefilter($this->getImage(), $filterType);
        }

        return $this;
    }

    public function blurImage(int $level = 10)
    {
        for ($i = 1; $i <= $level; $i++) {
            $this->filter(IMG_FILTER_GAUSSIAN_BLUR);
        }
    }

    public function writeFoot(
        string $string,
        int $fontSize,
        array $color,
        string $font
    ): void {
        echo $string . PHP_EOL;

        $getXPointNew = function(string $str, int $fontSize) {
            $strSize = strlen($str);
            preg_match_all('/([A-Z])/', $str, $matches);
            $capitalLetters = count($matches);

            $xPoint = $strSize * 0.133;
            $xPoint = $strSize * ($fontSize * 0.133);
            $xPoint = $strSize * 1.33;

            echo 'XPoint: ' . $xPoint . PHP_EOL;
            return $xPoint;
        };

        $height = $this->newHeight > 0 ? $this->newHeight : $this->getHeight($this->getImage());
        $width = $this->newWidth > 0 ? $this->newHeight : $this->getWidth($this->getImage());
        $yPoint = $height - $fontSize;
        $xPoint = (int) $width - (float) $getXPointNew($string, $fontSize);

        echo 'Width ' . $width . PHP_EOL;
        echo 'XPoint Calculated: ' . $xPoint . PHP_EOL;

        $this->writeWithFont(
            $string,
            $font,
            $fontSize,
            $color,
            intval(floor($xPoint)),
            intval(floor($yPoint))
        );
    }
}
