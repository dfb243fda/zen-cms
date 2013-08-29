<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

use ObjectTypes\Model\ObjectTypes as ObjectTypesModel;

class AddGroup extends AbstractMethod
{        
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        
        $result = array(
            'success' => 0,
        );
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {            
            $objectTypeId = (int)$this->params()->fromRoute('id');
            
            $tmp = $objectTypesModel->addGroup($objectTypeId, $this->params()->fromPost());
            
            if ($tmp['success']) {
                $result['msg'] = 'Группа успешно создана';
                $result['groupId'] = $tmp['groupId'];
                $result['name'] = $tmp['name'];
                $result['title'] = $tmp['title'];
                $result['success'] = true;
            } else {
                $result['success'] = false;
                $form = $tmp['form'];
                $result['formMsg'] = $form->getMessages();
            }
        }
        return $result;
    }
}