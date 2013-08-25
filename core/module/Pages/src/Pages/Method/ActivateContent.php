<?php

namespace Pages\Method;

use App\Method\AbstractMethod;

class ActivateContent extends AbstractMethod
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
            
            if ($contentEntity->setContentId($contentId)->activateContent()) {
                $result['success'] = true;
                $result['msg'] = 'Содержимое активировано';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При активации содержимого произошли проблемы';
            }
        }
        
        return $result;
    }
}