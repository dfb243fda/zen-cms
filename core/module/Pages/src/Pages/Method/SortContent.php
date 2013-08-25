<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class SortContent extends AbstractMethod
{
    public function main()
    {
        $result = array();
        
        if (null !== $this->params()->fromPost('beforeContentId') &&
            null !== $this->params()->fromPost('contentId') &&
            null !== $this->params()->fromPost('markerId')) {
            
            $beforeContentId = (int)$this->params()->fromPost('beforeContentId');
            $contentId = (int)$this->params()->fromPost('contentId');
            $markerId = (int)$this->params()->fromPost('markerId');
            
            $contentEntity = $this->serviceLocator->get('Pages\Entity\Content');
            
            $contentEntity->setContentId($contentId)->sortContent($beforeContentId, $markerId);
            
            $result['success'] = true;
        } else {
            $result['success'] = false;
        }
        
        return $result;
    }
}