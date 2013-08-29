<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;
use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class EditField extends AbstractMethod
{    
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('fieldId') && null !== $this->params()->fromRoute('groupId')) {            
            $fieldId = (int)$this->params()->fromRoute('fieldId');
            $groupId = (int)$this->params()->fromRoute('groupId');
            
            $tmp = $objectTypesModel->editField($fieldId, $groupId, $this->params()->fromPost());
            
            if ($tmp['success']) {
                $result['name'] = $tmp['name'];
                $result['title'] = $tmp['title'];
                $result['fieldTypeName'] = $tmp['fieldTypeName'];
                
                $result['success'] = true;
                $result['msg'] = 'Поле успешно изменено';
            } else {
                $result['success'] = false;
                $form = $tmp['form'];
                $result['formMsg'] = $form->getMessages();
            }
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        }
        
        return $result;
    }
    
}