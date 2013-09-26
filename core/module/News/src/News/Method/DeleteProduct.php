<?php

namespace Catalog\Method;

use App\Method\AbstractMethod;

class DeleteProduct extends AbstractMethod
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
          
        if ($catService->isObjectProduct($objectId)) {
            if ($objectsCollection->delObject($objectId)) {
                $result['msg'] = 'Товар удален';
                $result['success'] = true;
            } else {
                $result['errMsg'] = 'Не удалось удалить товар';
                $result['success'] = false;
            }
        } else {
            $result = array(
                'errMsg' => 'Товар ' . $objectId . ' не найден',
            );
        }
        
        return $result;
    }
}