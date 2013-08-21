<?php

namespace StandardPageContentTypes\Method;

use Pages\Entity\FeContentMethodInterface;

class SimpleText implements FeContentMethodInterface
{        
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