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
            $id = (int)$this->params()->fromPost('id');
            
            $result = $objectTypesCollection->delType($id);
            
            if (true == $result['success']) {
                $result['msg'] = 'Тип данных успешно удален';
            }
        }
        
        return $result;
    }
    
}