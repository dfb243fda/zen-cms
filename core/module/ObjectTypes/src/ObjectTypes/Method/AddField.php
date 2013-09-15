<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class AddField extends AbstractMethod
{    
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $formElementManager = $this->serviceLocator->get('formElementManager');
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('objectTypeId') && null !== $this->params()->fromRoute('groupId')) {            
            $objectTypeId = (int)$this->params()->fromRoute('objectTypeId');
            $groupId = (int)$this->params()->fromRoute('groupId');
            
            if (null === ($objectType = $objectTypesCollection->getType($objectTypeId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Не найден тип данных ' . $objectTypeId;
                return $result;
            }
            if (null === ($fieldsGroup = $objectType->getFieldsGroup($groupId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Группа полей ' . $groupId . ' не найдена';
                return $result;
            }
            
            $form = $formElementManager->get('ObjectTypes\Form\FieldAdminForm', array(
                'fieldsGroup' => $fieldsGroup,
            ));
            
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                $data = $form->getData();
                
                $fieldsAdminCollection = $this->serviceLocator->get('ObjectTypes\Collection\FieldsAdminCollection');
                
                $fieldsAdminCollection->setObjectTypeId($objectTypeId)
                                      ->setFieldsGroupId($groupId);
                
                $addResult = $fieldsAdminCollection->addField($data);
                if ($addResult['success']) {
                    $result['success'] = true;
                    $result['msg'] = 'Поле успешно добавлено';
                    
                    $field = $addResult['field'];
                    
                    $result['id'] = $addResult['fieldId'];
                    $result['name'] = $data['name'];
                    $result['title'] = $data['title'];
                    $result['fieldTypeName'] = $field->getFieldTypeName();
                } else {
                    $result['success'] = false;
                    $result['errMsg'] = $addResult['errMsg'];
                }                
            } else {
                $result['success'] = false;
                $result['formMsg'] = $form->getMessages();
            }
        } else {
            $result['success'] = false;
            $result['errMsg'] = 'Не переданы все необходимые параметры';
        }
        
        return $result;
    }
}