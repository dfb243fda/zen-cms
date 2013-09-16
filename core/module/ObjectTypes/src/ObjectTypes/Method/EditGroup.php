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
                
                $fieldsGroup->setName($data['name'])
                            ->setTitle($data['title'])
                            ->save();
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['success'] = true;
                $result['msg'] = 'Группа успешно обновлена';
            } else {
                $result['success'] = false;
                $result['formMsg'] = $form->getMessages();
            }
        }
        return $result;
    }
}