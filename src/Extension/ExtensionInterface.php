<?php

namespace Imanipuly\Extension;

interface ExtensionInterface
{
    public function open(string $filename): self;

    public function resize(int $newWidth, int $newHeight, string $option = 'auto'): self;
    
    public function crop(int $optimalWidth, int $optimalHeight, int $newWidth, int $newHeight): self;
    
    public function save(string $savePath, string $type = 'jpg', int $imageQuality = 100): self;

    public function blurImage(int $level = 10);

    public function filter(int $filterType, ?int $arg1 = null, ?int $arg2 = null, ?int $arg3 = null, ?int $arg4 = null);

    public function writeWithFont(
        string $string,
        string $font,
        int $fontSize,
        array $color,
        int $xPoint = 0,
        int $yPoint = 0,
        int $gravity = 0 
    ): self;
}
