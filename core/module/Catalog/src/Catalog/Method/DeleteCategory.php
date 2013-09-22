<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class DeleteCategory extends AbstractMethod
{
    public function main()
    {        
        $catService = $this->serviceLocator->get('Catalog\Service\Catalog');
        $objectsCollection = $this->serviceLocator->get('objectsCollection');
        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';            
            return $result;
        } 
        
        $objectId = (int)$this->params()->fromPost('id');
          
        if ($catService->isObjectCategory($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Категория удалена';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить категорию';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Категория ' . $objectId . ' не найдена',
            );
        }
        
        return $result;
    }
}