<?php
define('image/gif',  'GIF');
define('image/jpeg', 'JPG');
define('image/jpg',  'JPG');
define('image/pjpeg','JPG');
define('image/png',  'PNG');


/**
 * Class to manipulate image, like resize, fill color, rotate and write at image
 * @author Igor A. Carvalho
 * @version 0.0.1
 * @license MIT License
 * @copyright Copyright (C) 2010 - Open Source Matters. All rights reserved.
 *
 */
class Imanipuly {

    /**
     * Image path name
     * @var string
     */
    private $image = null;

    /**
     * Width of image
     * @var integer
     */
    private $width = 0;

    /**
     * Height of image
     * @var integer
     */
    private $height = 0;

    /**
     * Extension of image
     * @var string
     */
    private $extension = '';

    /**
     * Image to be manilulated, more expecific resized
     * @var object
     */
    private $imageResized = null;

    /**
     * Array of information of image, dimensions and mime type
     * @var array
     */
    private $imageInfo = array();

    /**
     * Array of max sizes of image to resize
     * @var array
     */
    private $scale = array();

    /**
     * Record image name
     * @var string
     */
    private $imageName = '';
    
    /**
     * Construct, set the path name of file to manipulate
     * @param $fileName[optional]
     * @return void
     */
    public function Imanipuly($fileName = '') {
        if($fileName != '') $this->open($fileName);
    }
    
    /**
     * Open image to initializethe process
     * @param $fileName
     * @return void
     */
    public function open($fileName) {
        $this->imageName = $fileName;
        
        $this->imageInfo = $this->getImageInfo($fileName);
        $this->extension = constant($this->imageInfo['mime']);
        
        $this->image = $this->load($fileName);

        $this->width = $this->getWidth($this->image);
        $this->height = $this->getHeight($this->image);
    }

    /**
     * Clean all objects attributes
     * @return void
     */
    public function clean() {
        $this->image = null;
        $this->width = 0;
        $this->height = 0;
        $this->extension = '';
        $this->imageResized = null;
        $this->imageInfo = array();
        $this->scale = array();
    }
    
    /**
     * Init object
     * @return void
     */
    public function init($image = '') {
        $this->clean();
        $this->imageName = (isset($image) && $image != '' ? $image : $this->imageName);
        $this->imageInfo = $this->getImageInfo($this->imageName);
        $this->extension = constant($this->imageInfo['mime']);
        
        $this->image = $this->load($this->imageName);

        $this->width = $this->getWidth($this->image);
        $this->height = $this->getHeight($this->image);
    }       
    
    /**
     * Load image to be manipulated, extensions supported, jpg, png and gif
     * @param string $image
     * @return object
     */
    public function load($image) {
        switch($this->extension) {
            case 'JPG': $img = imagecreatefromjpeg($image); break;
            case 'GIF': $img = imagecreatefromgif($image);  break;
            case 'PNG': $img = imagecreatefrompng($image);  break;
            default: $img = false; break;
        }
        return $img;
    }

    /**
     * Fill background color, with rgb code
     * @param integer $red
     * @param integer $green
     * @param integer $blue
     * @return void
     */
    public function fillColor($red = 0, $green = 0, $blue = 0) {
        $image = $this->getImage();
        $color = imagecolorallocate($image, $red, $green, $blue);
        imagefill($image, 0, 0, $color);
    }

    /**
     * Rezise image with some options to bo readed
     * Options (auto, portrait, scale, exact)
     * @param integer $newWidth
     * @param integer $newHeight
     * @param integer $option
     * @return void
     */
    public function resize($newWidth, $newHeight, $option = 'auto') {
        if($option == 'scale') $this->setMaxSizes($newWidth, $newHeight);
        $optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];

        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

        if($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    // TODO Fix method to render transparent border and read diverent corners
    /**
     * Round corners, fill corners color border
     * @param $radius
     * @param $colour
     * @return void
     */
    public function radius($radius = 20) {
        $corner_image = imagecreatetruecolor( $radius, $radius );
        $black = imagecolorallocate( $corner_image, 0, 0, 0 );
        $solid_colour = ImageColorAllocate($corner_image, 255, 255, 255);
        $transparentColor = imagecolorallocate($corner_image, 255, 0, 0);
        imagecolortransparent( $corner_image, $transparentColor );
        imagefill($corner_image, 0, 0, $solid_colour);
        imagefilledellipse($corner_image, $radius, $radius, $radius * 2, $radius * 2, $black);
        
        // TOP-LEFT
        imagecolortransparent($corner_image, $transparentColor);
        imagecopymerge( $this->getImage(), $corner_image, 0, 0, 0, 0, $radius, $radius, 100 );
        
        // TOP-RIGHT
        $corner_image = imagerotate( $corner_image, 90, 0 );
        imagecolortransparent($corner_image, $transparentColor);
        imagecopymerge( $this->getImage(), $corner_image, 0, $this->height - $radius, 0, 0, $radius, $radius, 100);
        
        // BOTTOM-LEFT
        $corner_image = imagerotate( $corner_image, 90, 0 );
        imagecolortransparent($corner_image, $transparentColor);
        imagecopymerge( $this->getImage(), $corner_image, $this->width - $radius, $this->height - $radius, 0, 0, $radius, $radius, 100 );
        
        // BOTTOM-RIGHT
        $corner_image = imagerotate( $corner_image, 90, 0 );
        imagecolortransparent($corner_image, $transparentColor);
        imagecopymerge( $this->getImage(), $corner_image, $this->width - $radius, 0, 0, 0, $radius, $radius, 100 );             
    }
    
    /**
     * Get dimensions by option selected
     * @param integer $newWidth
     * @param integer $newHeight
     * @param integer  $option
     * @return array
     */
    private function getDimensions($newWidth, $newHeight, $option) {
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
    private function getSizeByFixedHeight($newHeight) {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    /**
     * Get width size defined by height
     * @param integer $newWidth
     * @return integer
     */
    private function getSizeByFixedWidth($newWidth) {
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
    private function getSizeByAuto($newWidth, $newHeight) {
        if($this->height < $this->width) {
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
    private function getOptimalCrop($newWidth, $newHeight) {
        $heightRatio = $this->height / $this->width;
        $widthRatio = $this->width / $this->height;

        if($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }
        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth  = $this->width  / $optimalRatio;
         
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    /**
     * Method defined to crop image by dimensions seted
     * @param integer $optimalWidth
     * @param integer $optimalHeight
     * @param integer $newWidth
     * @param integer $newHeight
     * @return void
     */
    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight) {
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->getImage();

        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->getImage(), $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    /**
     * Method defined to save image at the path and quality setting
     * @param integer $savePath
     * @param integer $imageQuality
     * @return void
     */
    public function save($savePath, $type = 'jpg', $imageQuality = 100) {
        switch(strtoupper($type)) {
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
            default: break;
        }
        imagedestroy($this->getImage());
    }

    /**
     * Show image at broser without save file
     * @return void
     */
    public function show() {
        header('Content-type: '.$this->imageInfo['mime']);
        switch($this->extension) {
            case 'JPG': imagejpeg($this->getImage()); break;
            case 'GIF': imagegif($this->getImage());  break;
            case 'PNG': imagepng($this->getImage());  break;
        }
    }

    /**
     * Get RGB color at determinated pixel (x and y coordinates)
     * @param integer $xPoint
     * @param integer $yPoint
     * @return array
     */
    public function getPixelColor($xPoint, $yPoint) {
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
    public function write($xPoint = 0, $yPoint = 0, $string, $fontSize, $color) {
        $textColor = imagecolorallocate($this->getImage(), $color['red'], $color['green'], $color['blue']);
        imagestring($this->getImage(), $fontSize, $xPoint, $yPoint, $string, $textColor);
    }

    /**
     * Get image with grey or colors RGB channels defined
     * @param string $channel
     * @param string $type
     * @return void
     */
    public function separeChannels($channel, $type = 'grey') {
        for($i = 0; $i < $this->width; $i++) {
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
                if($type == 'grey') $px = imagecolorallocate($this->getImage(), $new, $new, $new);
                imagesetpixel($this->getImage(), $i, $ii, $px);
            }
        }
    }

    /**
     * Method defined to rotate image
     * @param integer $degrees
     * @return void
     */
    public function rotate($degrees) {
        $image = $this->getImage();
        $this->imageResized = imagerotate($image, $degrees, 0);
    }

    /**
     * Method define to get actual image manipulating inside object
     * @return object
     */
    private function getImage() {
        if(isset($this->imageResized)) $image = $this->imageResized;
        else $image = $this->image;
        return $image;
    }

    /**
     * Get width of image
     * @param string $image
     * @return integer
     */
    public function getWidth($image) {
        return imagesx($image);
    }

    /**
     * Get height of image
     * @param string $image
     * @return integer
     */
    public function getHeight($image) {
        return imagesy($image);
    }

    /**
     * Get array of image info, like dimensions and mime-type
     * @param string $image
     * @return array
     */
    private function getImageInfo($image) {
        return getimagesize($image);
    }

    /**
     * Determine max sizes to resize image
     * @param integer $maxWidth
     * @param integer $maxHeigh
     * @return void
     */
    public function setMaxSizes($maxWidth = 0, $maxHeigh = 0) {
        $this->scale['maxWidth'] = ($maxWidth > 0) ? $maxWidth : $this->width;
        $this->scale['maxHeight'] = ($maxHeight > 0) ? $maxHeight : $this->height;
    } 

}