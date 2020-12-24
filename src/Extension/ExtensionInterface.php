<?php

namespace Imanipuly\Extension;

interface ExtensionInterface
{
    public function resize(int $newWidth, int $newHeight, string $option = 'auto'): self;
    
    public function crop(int $optimalWidth, int $optimalHeight, int $newWidth, int $newHeight): self;
    
    public function save($savePath, string $type = 'jpg', int $imageQuality = 100): self;
}