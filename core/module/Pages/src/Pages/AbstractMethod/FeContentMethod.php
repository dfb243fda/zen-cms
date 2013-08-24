<?php

namespace Pages\AbstractMetod;

use App\Method\AbstractMethod;

abstract class FeContentMethod extends AbstractMethod implements FeContentMethodInterface
{
    protected $contentData;
    
    public function setContentData($contentData)
    {
        $this->contentData = $contentData;
    }
}