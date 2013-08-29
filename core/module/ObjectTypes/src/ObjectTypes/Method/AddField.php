<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class AddField extends AbstractMethod
{    
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId') && null !== $this->params()->fromRoute('groupId')) {            
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $groupId = (int)$this->params()->fromRoute('groupId');
            
            $tmp = $objectTypesModel->addField($objectTypeId, $groupId, $this->params()->fromPost());
            
            if ($tmp['success']) {
                $result['id'] = $tmp['id'];
                $result['name'] = $tmp['name'];
                $result['title'] = $tmp['title'];
                $result['fieldTypeName'] = $tmp['fieldTypeName'];
                
                $result['success'] = true;
                $result['msg'] = 'Поле успешно добавлено';
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