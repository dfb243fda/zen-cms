<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class DeactivateContent extends AbstractMethod
{    
    public function main()
    {
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан параметр id';
        } else {
            $contentId = (int)$this->params()->fromPost('id');
            
            $contentEntity = $this->serviceLocator->get('Pages\Entity\Content');
            
            if ($contentEntity->setContentId($contentId)->deactivateContent()) {
                $result['success'] = true;
                $result['msg'] = 'Содержимое деактивировано';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При деактивации содержимого произошли проблемы';
            }
        }
        
        return $result;
    }
}