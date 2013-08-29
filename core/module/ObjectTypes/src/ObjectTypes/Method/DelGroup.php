<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelGroup extends AbstractMethod
{    
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        
        $result = array(
            'success' => false,
        );
        if (null === $this->params()->fromPost('groupId') || null === $this->params()->fromPost('objectTypeId')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $objectTypeId = (int)$this->params()->fromPost('objectTypeId');
            $groupId = (int)$this->params()->fromPost('groupId');
            
            $type = $objectTypesCollection->getType($objectTypeId);
                
            if (false === $type) {
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не найден';
                return $result;
            }
            else {
                if ($type->delFieldsGroup($groupId)) {
                    $result['msg'] = 'Группа успешно удалена';
                    $result['success'] = 1;
                } else {
                    $result['errMsg'] = 'При удалении группы произошли ошибки';
                }
            }
        }
        return $result;
    }
    
}