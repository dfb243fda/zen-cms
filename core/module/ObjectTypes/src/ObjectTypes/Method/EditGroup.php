<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class EditGroup extends AbstractMethod
{    
    public function main()
    {       
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $formElementManager = $this->serviceLocator->get('formElementManager');
        
        $result = array(
            'success' => false,
        );
        if (null === $this->params()->fromRoute('groupId') || null === $this->params()->fromRoute('objectTypeId')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $groupId = (int)$this->params()->fromRoute('groupId');
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            
            if (null === ($objectType = $objectTypesCollection->getType($objectTypeId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Тип данных ' . $objectTypeId . ' не найден';
                return $result;
            }
            if (null === ($fieldsGroup = $objectType->getFieldsGroup($groupId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Группа ' . $fieldsGroup . ' не найдена';
                return $result;
            }
            
            $form = $formElementManager->get('ObjectTypes\Form\FieldsGroupAdminForm', array(
                'fieldsGroupCollection' => $objectType->getFieldsGroupCollection(),
                'groupId' => $groupId,
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

class EditGroup2 extends AbstractMethod
{    
    public function main()
    {
        $objectTypesModel = new ObjectTypesModel($this->serviceLocator);
        
        $result = array(
            'success' => 0,
        );
        if (null === $this->params()->fromRoute('id') || null === $this->params()->fromPost('name') || null === $this->params()->fromPost('title')) {
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        } 
        else {
            $groupId = (int)$this->params()->fromRoute('id');
            
            $tmp = $objectTypesModel->editGroup($groupId, $this->params()->fromPost());
            
            if ($tmp['success']) {
                $result['msg'] = 'Группа успешно обновлена';
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