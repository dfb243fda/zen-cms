<?php

namespace ObjectTypes\Method;

use App\Method\AbstractMethod;

class EditField extends AbstractMethod
{    
    public function main()
    {
        $objectTypesCollection = $this->serviceLocator->get('objectTypesCollection');
        $formElementManager = $this->serviceLocator->get('formElementManager');
        $fieldsCollection = $this->serviceLocator->get('fieldsCollection');
        
        $result = array();
        
        if (null !== $this->params()->fromRoute('fieldId') && null !== $this->params()->fromRoute('groupId')) {            
            $fieldId = (int)$this->params()->fromRoute('fieldId');
            $groupId = (int)$this->params()->fromRoute('groupId');
            
            $fieldsGroup = $this->serviceLocator->get('App\Field\FieldsGroup');
            $fieldsGroup->setId($groupId);
            
            if (!$fieldsGroup->isExists()) {
                $result['success'] = false;
                $result['errMsg'] = 'Группа ' . $groupId . ' не найдена';
                return $result;
            }
            
            $fieldsGroup->loadFields();
            
            if (null === ($field = $fieldsGroup->getField($fieldId))) {
                $result['success'] = false;
                $result['errMsg'] = 'Поле ' . $fieldId . ' не найдено';
                return $result;
            }
            
            $form = $formElementManager->get('ObjectTypes\Form\FieldAdminForm', array(
                'fieldsGroup' => $fieldsGroup,
                'fieldId' => $fieldId,
            ));
            
            $form->setData($this->params()->fromPost());
            
            if ($form->isValid()) {
                $data = $form->getData();
                
                $field->setFieldData($data)->save();
                $result['name'] = $data['name'];
                $result['title'] = $data['title'];
                $result['fieldTypeName'] = $field->getFieldTypeName();
                
                $result['success'] = true;
                $result['msg'] = 'Поле успешно изменено';
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