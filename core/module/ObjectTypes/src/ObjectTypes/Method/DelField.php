<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class DelField extends AbstractMethod
{
    public function main()
    {        
        $result = array();
        
        if (null !== $this->params()->fromPost('fieldId') &&
                null !== $this->params()->fromPost('groupId'))
        {        
            $fieldId = (int)$this->params()->fromPost('fieldId');
            $groupId = (int)$this->params()->fromPost('groupId');  
            
            $fieldsGroup = $this->serviceLocator->get('App\Field\FieldsGroup');
            $fieldsGroup->setId($groupId);
            
            if (!$fieldsGroup->isExists()) {
                $result['success'] = false;
                $result['errMsg'] = 'Группа ' . $groupId . ' не найдена';
                return $result;
            }
            
            $fieldsGroup->loadFields();
            
            if (!$fieldsGroup->getField($fieldId)) {
                $result['success'] = false;
                $result['errMsg'] = 'Поле ' . $fieldId . ' не найдено';
                return $result;
            }

            $fieldsGroup->detachField($fieldId);
            
            $result['msg'] = 'Поле успешно удалено';
            $result['success'] = true;
        } else {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
            $result['success'] = false;
        }
        
        return $result;
    }
}