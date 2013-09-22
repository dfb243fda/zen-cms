<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class DeleteMenuItem extends AbstractMethod
{
    public function main()
    {        
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromPost('id');
          
        if ($menuService->isObjectItem($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Пункт меню удален';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить пункт меню';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Пункт меню ' . $objectId . ' не найден',
            );
        }
        
        return $result;
    }
}