<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelObjectType extends AbstractMethod
{    
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        
        $result = array();
        
        if (null === $this->params()->fromPost('id')) {
            $result['success'] = false;
            $result['errMsg'] = 'Не передан id типа данных';
        }
        else {
            $typeId = (int)$this->params()->fromPost('id');
            
            if (null === ($objectType = $objectTypesCollection->getType($typeId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Не найден тип данных ' . $typeId;
                return $result;
            }
            if ($objectType->getIsLocked()) {
                $result['success'] = false;
                $result['errMsg'] = 'Ттип данных ' . $typeId . ' заблокирован';
                return $result;
            }
            
            if ($objectTypesCollection->delType($typeId)) {
                $result['success'] = true;
                $result['msg'] = 'Тип данных успешно удален';
            } else {
                $result['success'] = false;
                $result['errMsg'] = 'При удалении типа данных произошли ошибки';
            }
        }
        
        return $result;
    }
    
}