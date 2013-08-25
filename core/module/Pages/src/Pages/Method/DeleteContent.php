<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeleteContent extends AbstractMethod
{
    public function main()
    {
        $result = array();
        
        $contentCollection = $this->serviceLocator->get('Pages\Collection\Content');
        
        if (null === $this->params()->fromPost('id')) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан параметр id';
        } else {
            $contentId = (int)$this->params()->fromPost('id');
            
            if ($contentCollection->deleteContent($contentId)) {
                $result['success'] = true;
                $result['msg'] = 'Содержимое удалено';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При удалении содержимого произошли проблемы';
            }
        }
        
        return $result;
    }    
}