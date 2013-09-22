<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class DeleteMenu extends AbstractMethod
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
          
        if ($menuService->isObjectMenu($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Меню удалено';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить меню';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Меню ' . $objectId . ' не найдено',
            );
        }
        
        return $result;
    }
}