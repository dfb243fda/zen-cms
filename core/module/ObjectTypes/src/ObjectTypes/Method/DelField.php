<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelField extends AbstractMethod
{
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        
        $result = array(
            'success' => 0,
        );
        
        if (null !== $this->params()->fromPost('fieldId') &&
                null !== $this->params()->fromPost('objectTypeId') && 
                null !== $this->params()->fromPost('groupId'))
        {        
            $fieldId = (int)$this->params()->fromPost('fieldId');
            $objectTypeId = (int)$this->params()->fromPost('objectTypeId');
            $groupId = (int)$this->params()->fromPost('groupId');            

            $objectTypesCollection->getType($objectTypeId)->getFieldsGroup($groupId)->detachField($fieldId);
            
            $result['msg'] = 'Поле успешно удалено';
            $result['success'] = 1;
        } else {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        }
        
        return $result;
    }
}