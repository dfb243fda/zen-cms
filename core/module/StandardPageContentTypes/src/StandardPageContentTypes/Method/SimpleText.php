<?php

namespace StandardPageContentTypes\Method;

use Pages\AbstractMethod\FeContentMethodInterface;

class SimpleText implements FeContentMethodInterface
{        
    protected $contentData;
    
    public function setContentData($contentData)
    {
        $this->contentData = $contentData;
    }
    
    public function init()
    {
        
    }
    
    public function main()
    {
        return array(
            'objectId' => $this->contentData['object_id'],
        );
    }
}