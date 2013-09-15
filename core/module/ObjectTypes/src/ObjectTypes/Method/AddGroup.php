<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class AddGroup extends AbstractMethod
{        
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $formElementManager = $this->serviceLocator->get('formElementManager');
        
        $result = array(
            'success' => false,
        );
        if (null === $this->params()->fromRoute('id')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {            
            $objectTypeId = (int)$this->params()->fromRoute('id');
            
            if (null === ($objectType = $objectTypesCollection->getType($objectTypeId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Не найден тип данных ' . $objectTypeId;
                return $result;
            }
            
            $form = $formElementManager->get('ObjectTypes\Form\FieldsGroupAdminForm', array(
                'objectType' => $objectType,
            ));
            
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                $data = $form->getData();
                
                $result['groupId'] = $objectType->addFieldsGroup($data['name'], $data['title']);
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['success'] = true;
                $result['msg'] = 'Группа успешно создана';
            } else {
                $result['success'] = false;
                $result['formMsg'] = $form->getMessages();
            }
        }
        return $result;
    }
}