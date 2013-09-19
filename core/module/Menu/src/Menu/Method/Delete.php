<?php

namespace Menu\Method;

use App\Method\AbstractMethod;

class Delete extends AbstractMethod
{
    public function main()
    {        
        $menuService = $this->serviceLocator->get('Menu\Service\Menu');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        if (null === $this->params()->fromPost('id')) {
            $result = array(
                'errMsg' => 'Не переданы все необходимые параметры',
            );
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
        } elseif ($menuService->isObjectItem($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Пункт меню удален';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить пункт меню';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Объект ' . $objectId . ' не является ни меню, ни пунктом меню',
            );
        }
        
        return $result;
    }
}